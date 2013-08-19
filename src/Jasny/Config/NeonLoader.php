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
 * Config loader for Nette Object Notation.
 */
class NeonLoader
{
    use LoadFile;
    
    const BLOCK = 1;

    /** @var array */
    protected static $patterns = [
        '
			\'[^\'\n]*\' |
			"(?: \\\\. | [^"\\\\\n] )*"
		', // string
        '
			(?: [^#"\',:=[\]{}()\x00-\x20!`-] | [:-][^"\',\]})\s] )
			(?:
				[^,:=\]})(\x00-\x20]+ |
				:(?! [\s,\]})] | $ ) |
				[\ \t]+ [^#,:=\]})(\x00-\x20]
			)*
		', // literal / boolean / integer / float
        '
			[,:=[\]{}()-]
		', // symbol
        '?:\#.*', // comment
        '\n[\t\ ]*', // new line + indent
        '?:[\t\ ]+', // whitespace
    ];

    /** @var string */
    protected static $re;
    protected static $brackets = [
        '[' => ']',
        '{' => '}',
        '(' => ')',
    ];

    /** @var string */
    protected $input;

    /** @var array */
    public $tokens;

    /** @var int */
    protected $n = 0;

    /** @var bool */
    protected $indentTabs;

    /**
     * Parse a NEON file.
     * 
     * @param  string
     * @return mixed
     */
    public function parse($input)
    {
        if (!is_string($input)) {
            throw new InvalidArgumentException("Argument must be a string, " . gettype($input) . " given.");
        }
        if (!self::$re) {
            self::$re = '~(' . implode(')|(', self::$patterns) . ')~Amix';
        }

        if (substr($input, 0, 3) === "\xEF\xBB\xBF") { // BOM
            $input = substr($input, 3);
        }

        $this->tokenize($input);
        $res = $this->parseString(0);

        while (isset($this->tokens[$parser->n])) {
            if ($parser->tokens[$parser->n][0] === "\n") {
                $this->n++;
            } else {
                $this->error();
            }
        }
        return $res;
    }

    protected function tokenize($input)
    {
        $this->input = str_replace("\r", '', $input);
        $this->tokens = preg_split(self::$re, $this->input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        if ($code = preg_last_error()) {
            trigger_error("PREG error ($code)", E_USER_WARNING);
            return null;
        }
        if ($this->tokens && !preg_match(self::$re, end($this->tokens))) {
            $this->n = count($this->tokens) - 1;
            $this->error();
        }
    }

    /**
     * Parse a neon string
     * 
     * @param  int  indentation (for block-parser)
     * @param  mixed
     * @return array
     */
    protected function parseString($indent = null, $result = null)
    {
        $inlineParser = $indent === null;
        $value = $key = $object = null;
        $hasValue = $hasKey = false;
        $tokens = $this->tokens;
        $n = & $this->n;
        $count = count($tokens);

        for (; $n < $count; $n++) {
            $t = $tokens[$n];

            if ($t === ',') { // ArrayEntry separator
                if ((!$hasKey && !$hasValue) || !$inlineParser) {
                    $this->error();
                }
                $this->addValue($result, $hasKey, $key, $hasValue ? $value : null);
                $hasKey = $hasValue = false;
            } elseif ($t === ':' || $t === '=') { // KeyValuePair separator
                if ($hasKey || !$hasValue) {
                    $this->error();
                }
                if (is_array($value) || is_object($value)) {
                    $this->error('Unacceptable key');
                }
                $key = (string)$value;
                $hasKey = true;
                $hasValue = false;
            } elseif ($t === '-') { // BlockArray bullet
                if ($hasKey || $hasValue || $inlineParser) {
                    $this->error();
                }
                $key = null;
                $hasKey = true;
            } elseif (isset(self::$brackets[$t])) { // Opening bracket [ ( {
                if ($hasValue) {
                    if ($t !== '(') {
                        $this->error();
                    }
                    $n++;
                    $entity = new NeonEntity;
                    $entity->value = $value;
                    $entity->attributes = $this->parseString(NULL, []);
                    $value = $entity;
                } else {
                    $n++;
                    $value = $this->parseString(NULL, []);
                }
                $hasValue = true;
                if (!isset($tokens[$n]) || $tokens[$n] !== self::$brackets[$t]) {
                    // unexpected type of bracket or block-parser
                    $this->error();
                }
            } elseif ($t === ']' || $t === '}' || $t === ')') { // Closing bracket ] ) }
                if (!$inlineParser) {
                    $this->error();
                }
                break;
            } elseif ($t[0] === "\n") { // Indent
                if ($inlineParser) {
                    if ($hasKey || $hasValue) {
                        $this->addValue($result, $hasKey, $key, $hasValue ? $value : null);
                        $hasKey = $hasValue = false;
                    }
                } else {
                    while (isset($tokens[$n + 1]) && $tokens[$n + 1][0] === "\n") $n++; // skip to last indent
                    if (!isset($tokens[$n + 1])) {
                        break;
                    }

                    $newIndent = strlen($tokens[$n]) - 1;
                    if ($indent === null) { // first iteration
                        $indent = $newIndent;
                    }
                    if ($newIndent) {
                        if ($this->indentTabs === null) {
                            $this->indentTabs = $tokens[$n][1] === "\t";
                        }
                        if (strpos($tokens[$n], $this->indentTabs ? ' ' : "\t")) {
                            $n++;
                            $this->error('Either tabs or spaces may be used as indenting chars, but not both.');
                        }
                    }

                    if ($newIndent > $indent) { // open new block-array or hash
                        if ($hasValue || !$hasKey) {
                            $n++;
                            $this->error('Unexpected indentation.');
                        } else {
                            $this->addValue($result, $key !== null, $key, $this->parse($newIndent));
                        }
                        $newIndent = isset($tokens[$n]) ? strlen($tokens[$n]) - 1 : 0;
                        $hasKey = false;
                    } else {
                        if ($hasValue && !$hasKey) { // block items must have "key"; null key means list item
                            break;
                        } elseif ($hasKey) {
                            $this->addValue($result, $key !== null, $key, $hasValue ? $value : null);
                            $hasKey = $hasValue = false;
                        }
                    }

                    if ($newIndent < $indent) { // close block
                        return $result; // block parser exit point
                    }
                }
            } else { // Value
                if ($hasValue) {
                    $this->error();
                }
                static $consts = [
                    'true' => true, 'True' => true, 'TRUE' => true, 'yes' => true, 'Yes' => true, 'YES' => true,
                    'on' => true, 'On' => true, 'ON' => true,
                    'false' => false, 'False' => false, 'FALSE' => false, 'no' => false, 'No' => false, 'NO' => false,
                    'off' => false, 'Off' => false, 'OFF' => false,
                ];
                if ($t[0] === '"') {
                    $value = preg_replace_callback('#\\\\(?:u[0-9a-f]{4}|x[0-9a-f]{2}|.)#i', [$this, 'cbString'],
                        substr($t, 1, -1));
                } elseif ($t[0] === "'") {
                    $value = substr($t, 1, -1);
                } elseif (isset($consts[$t])) {
                    $value = $consts[$t];
                } elseif ($t === 'null' || $t === 'Null' || $t === 'NULL') {
                    $value = null;
                } elseif (is_numeric($t)) {
                    $value = $t * 1;
                } elseif (preg_match('#\d\d\d\d-\d\d?-\d\d?(?:(?:[Tt]| +)\d\d?:\d\d:\d\d(?:\.\d*)? ' .
                        '*(?:Z|[-+]\d\d?(?::\d\d)?)?)?\z#A', $t)) {
                    $value = new DateTime($t);
                } else { // literal
                    $value = $t;
                }
                $hasValue = true;
            }
        }

        if ($inlineParser) {
            if ($hasKey || $hasValue) {
                $this->addValue($result, $hasKey, $key, $hasValue ? $value : null);
            }
        } else {
            if ($hasValue && !$hasKey) { // block items must have "key"
                if ($result === null) {
                    $result = $value; // simple value parser
                } else {
                    $this->error();
                }
            } elseif ($hasKey) {
                $this->addValue($result, $key !== null, $key, $hasValue ? $value : null);
            }
        }
        return $result;
    }

    protected function addValue(&$result, $hasKey, $key, $value)
    {
        if ($hasKey) {
            if ($result && array_key_exists($key, $result)) {
                $this->error("Duplicated key '$key'");
            }
            $result[$key] = $value;
        } else {
            $result[] = $value;
        }
    }

    protected function cbString($m)
    {
        static $mapping = ['t' => "\t", 'n' => "\n", 'r' => "\r", 'f' => "\x0C", 'b' => "\x08", '"' => '"',
            '\\' => '\\', '/' => '/', '_' => "\xc2\xa0"];
        $sq = $m[0];
        if (isset($mapping[$sq[1]])) {
            return $mapping[$sq[1]];
        } elseif ($sq[1] === 'u' && strlen($sq) === 6) {
            return iconv('UTF-32BE', 'UTF-8//IGNORE', pack('N', hexdec(substr($sq, 2))));
        } elseif ($sq[1] === 'x' && strlen($sq) === 4) {
            return chr(hexdec(substr($sq, 2)));
        } else {
            $this->error("Invalid escaping sequence $sq");
        }
    }

    protected function error($message = "Unexpected '%s'")
    {
        $tokens = preg_split(self::$re, $this->input, -1,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_OFFSET_CAPTURE | PREG_SPLIT_DELIM_CAPTURE);
        $offset = isset($tokens[$this->n]) ? $tokens[$this->n][1] : strlen($this->input);
        $line = $offset ? substr_count($this->input, "\n", 0, $offset) + 1 : 1;
        $col = $offset - strrpos(substr($this->input, 0, $offset), "\n");
        $token = isset($this->tokens[$this->n]) ?
            str_replace("\n", '<new line>', substr($this->tokens[$this->n], 0, 40)) :
            'end';
        
        trigger_error(str_replace('%s', $token, $message) . " on line $line, column $col.", E_USER_WARNING);
    }
}

/**
 * Representation of 'foo(bar=1)' literal
 */
class NeonEntity extends stdClass
{
    public $value;
    public $attributes;
}
