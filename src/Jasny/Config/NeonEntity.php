<?php
/**
 * Jasny Config - Configure your application.
 *
 * Neon is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * @author David Grudl
 * @author Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/nette/nette/master/license.md BSD
 */
/** */
namespace Jasny\Config;

/**
 * Representation of 'foo(bar=1)' literal
 */
class NeonEntity extends \stdClass
{
    public $value;
    public $attributes;
}
