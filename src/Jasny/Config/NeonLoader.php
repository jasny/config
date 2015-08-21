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

use Nette\Neon;

/**
 * Config loader for Nette Object Notation.
 */
class NeonLoader extends Loader
{
    use LoadFile;
    
    /**
     * Parse a NEON string.
     * 
     * @param  string
     * @return mixed
     */
    public function parse($input)
    {
        return Neon\Decoder::decode($input);
    }
}

