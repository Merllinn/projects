<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @package Nette\Security
 */



/**
 * Trivial implementation of IAuthenticator.
 *
 * @author     David Grudl
 */
class MyAuthenticator extends NObject implements IAuthenticator
{
	/** @var array */
	private $userlist;


	/**
	 * Performs an authentication against e.g. database.
	 * and returns IIdentity on success or throws NAuthenticationException
	 * @param  array
	 * @return IIdentity
	 * @throws NAuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;
        $row = dibi::fetch("SELECT username_users, password_users, role_users, maxonline FROM users, companies WHERE [companies_id_companies] = [id_companies] AND username_users=%s", $username);

        if (!$row) { // uživatel nenalezen?
            throw new NAuthenticationException("username");
        }
        if ($row->maxonline != 1){ // uzivatel je zablokovan
            throw new NAuthenticationException("notActivated");
        }
        if ($row->password_users !== $password&&$row->password_users<>""&&$password<>"") { // hesla se neshodují?
            throw new NAuthenticationException("password");
        }

        return new NIdentity($row->username_users, $row->role_users); 
	}

}
