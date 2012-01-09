<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @package Nette\Application
 */



/**
 * Front Controller.
 *
 * @author     David Grudl
 */
class NApplication extends NObject
{
	/** @var int */
	public static $maxLoop = 20;

	/** @var bool enable fault barrier? */
	public $catchExceptions;

	/** @var string */
	public $errorPresenter;

	/** @var array of function(Application $sender); Occurs before the application loads presenter */
	public $onStartup;

	/** @var array of function(Application $sender, Exception $e = NULL); Occurs before the application shuts down */
	public $onShutdown;

	/** @var array of function(Application $sender, Request $request); Occurs when a new request is ready for dispatch */
	public $onRequest;

	/** @var array of function(Application $sender, IResponse $response); Occurs when a new response is received */
	public $onResponse;

	/** @var array of function(Application $sender, Exception $e); Occurs when an unhandled exception occurs in the application */
	public $onError;

	/** @var array of string */
	public $allowedMethods = array('GET', 'POST', 'HEAD', 'PUT', 'DELETE');

	/** @var array of NPresenterRequest */
	private $requests = array();

	/** @var IPresenter */
	private $presenter;

	/** @var IDiContainer */
	private $context;



	public function __construct(IDiContainer $context)
	{
		$this->context = $context;
	}



	/**
	 * Dispatch a HTTP request to a front controller.
	 * @return void
	 */
	public function run()
	{
		$httpRequest = $this->context->httpRequest;
		$httpResponse = $this->context->httpResponse;

		// check HTTP method
		if ($this->allowedMethods) {
			$method = $httpRequest->getMethod();
			if (!in_array($method, $this->allowedMethods, TRUE)) {
				$httpResponse->setCode(IHttpResponse::S501_NOT_IMPLEMENTED);
				$httpResponse->setHeader('Allow', implode(',', $this->allowedMethods));
				echo '<h1>Method ' . htmlSpecialChars($method) . ' is not implemented</h1>';
				return;
			}
		}

		// dispatching
		$request = NULL;
		$repeatedError = FALSE;
		do {
			try {
				if (count($this->requests) > self::$maxLoop) {
					throw new NApplicationException('Too many loops detected in application life cycle.');
				}

				if (!$request) {
					$this->onStartup($this);

					// routing
					$router = $this->getRouter();

					// enable routing debugger
					NRoutingDebugger::initialize($this, $httpRequest);

					$request = $router->match($httpRequest);
					if (!$request instanceof NPresenterRequest) {
						$request = NULL;
						throw new NBadRequestException('No route for HTTP request.');
					}

					if (strcasecmp($request->getPresenterName(), $this->errorPresenter) === 0) {
						throw new NBadRequestException('Invalid request. Presenter is not achievable.');
					}
				}

				$this->requests[] = $request;
				$this->onRequest($this, $request);

				// Instantiate presenter
				$presenterName = $request->getPresenterName();
				try {
					$this->presenter = $this->getPresenterFactory()->createPresenter($presenterName);
				} catch (NInvalidPresenterException $e) {
					throw new NBadRequestException($e->getMessage(), 404, $e);
				}

				$this->getPresenterFactory()->getPresenterClass($presenterName);
				$request->setPresenterName($presenterName);
				$request->freeze();

				// Execute presenter
				$response = $this->presenter->run($request);
				$this->onResponse($this, $response);

				// Send response
				if ($response instanceof NForwardResponse) {
					$request = $response->getRequest();
					continue;

				} elseif ($response instanceof IPresenterResponse) {
					$response->send($httpRequest, $httpResponse);
				}
				break;

			} catch (Exception $e) {
				// fault barrier
				$this->onError($this, $e);

				if (!$this->catchExceptions) {
					$this->onShutdown($this, $e);
					throw $e;
				}

				if ($repeatedError) {
					$e = new NApplicationException('An error occurred while executing error-presenter', 0, $e);
				}

				if (!$httpResponse->isSent()) {
					$httpResponse->setCode($e instanceof NBadRequestException ? $e->getCode() : 500);
				}

				if (!$repeatedError && $this->errorPresenter) {
					$repeatedError = TRUE;
					if ($this->presenter instanceof NPresenter) {
						try {
							$this->presenter->forward(":$this->errorPresenter:", array('exception' => $e));
						} catch (NAbortException $foo) {
							$request = $this->presenter->getLastCreatedRequest();
						}
					} else {
						$request = new NPresenterRequest(
							$this->errorPresenter,
							NPresenterRequest::FORWARD,
							array('exception' => $e)
						);
					}
					// continue

				} else { // default error handler
					if ($e instanceof NBadRequestException) {
						$code = $e->getCode();
					} else {
						$code = 500;
						NDebugger::log($e, NDebugger::ERROR);
					}
					require dirname(__FILE__) . '/templates/error.phtml';
					break;
				}
			}
		} while (1);

		$this->onShutdown($this, isset($e) ? $e : NULL);
	}



	/**
	 * Returns all processed requests.
	 * @return array of Request
	 */
	final public function getRequests()
	{
		return $this->requests;
	}



	/**
	 * Returns current presenter.
	 * @return IPresenter
	 */
	final public function getPresenter()
	{
		return $this->presenter;
	}



	/********************* services ****************d*g**/



	/**
	 * @internal
	 */
	protected function getContext()
	{
		return $this->context;
	}



	/**
	 * Returns router.
	 * @return IRouter
	 */
	public function getRouter()
	{
		return $this->context->router;
	}



	/**
	 * Returns presenter factory.
	 * @return IPresenterFactory
	 */
	public function getPresenterFactory()
	{
		return $this->context->presenterFactory;
	}



	/********************* request serialization ****************d*g**/



	/**
	 * Stores current request to session.
	 * @param  mixed  optional expiration time
	 * @return string key
	 */
	public function storeRequest($expiration = '+ 10 minutes')
	{
		$session = $this->context->session->getSection('Nette.Application/requests');
		do {
			$key = NStrings::random(5);
		} while (isset($session[$key]));

		$session[$key] = end($this->requests);
		$session->setExpiration($expiration, $key);
		return $key;
	}



	/**
	 * Restores current request to session.
	 * @param  string key
	 * @return void
	 */
	public function restoreRequest($key)
	{
		$session = $this->context->session->getSection('Nette.Application/requests');
		if (isset($session[$key])) {
			$request = clone $session[$key];
			unset($session[$key]);
			$request->setFlag(NPresenterRequest::RESTORED, TRUE);
			$this->presenter->sendResponse(new NForwardResponse($request));
		}
	}

}
