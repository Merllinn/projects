<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @package Nette\Latte\Macros
 */



/**
 * Macros for NForms.
 *
 * - {form name} ... {/form}
 * - {input name}
 * - {label name /} or {label name}... {/label}
 *
 * @author     David Grudl
 */
class NFormMacros extends NMacroSet
{

	public static function install(NParser $parser)
	{
		$me = new self($parser);
		$me->addMacro('form',
			'$form = $control[%node.word]; echo $form->getElementPrototype()->addAttributes(%node.array)->startTag()',
			'?><div><?php
foreach ($form->getComponents(TRUE, \'NHiddenField\') as $_tmp) echo $_tmp->getControl();
if (iterator_count($form->getComponents(TRUE, \'NTextInput\')) < 2) echo "<!--[if IE]><input type=IEbug disabled style=\"display:none\"><![endif]-->";
?></div>
<?php echo $form->getElementPrototype()->endTag()');
		$me->addMacro('label', array($me, 'macroLabel'), '?></label><?php');
		$me->addMacro('input', 'echo $form[%node.word]->getControl()->addAttributes(%node.array)');
	}



	/********************* macros ****************d*g**/


	/**
	 * {label ...} and optionally {/label}
	 */
	public function macroLabel(NMacroNode $node, $writer)
	{
		$cmd = 'if ($_label = $form[%node.word]->getLabel()) echo $_label->addAttributes(%node.array)';
		if ($node->isEmpty = (substr($node->args, -1) === '/')) {
			$node->setArgs(substr($node->args, 0, -1));
			return $writer->write($cmd);
		} else {
			return $writer->write($cmd . '->startTag()');
		}
	}

}
