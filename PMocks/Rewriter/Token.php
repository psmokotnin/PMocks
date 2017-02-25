<?php
namespace PMocks\Rewriter;
/**
 * Wraper for token data. 
 * token_get_all() return array of tokens. Each token can be an array or a single char symbol.
 * used by \PMocks\Rewriter
 */
class Token
{
    const T_SINGLECHAR = 10001;
    const NAME_SINGLECHAR = 'single character';
    
    protected
        $data;
    
    /**
     * __construct function.
     * 
     * @access public
     * @param array|string $token
     * @param int $line (default: NULL) for characters
     */
    public function __construct($token, $line = NULL)
    {
        if (is_string($token)) {
            $this->data = array(
                0 => self::T_SINGLECHAR,
                1 => $token,
                2 => $line
            );
        } elseif (is_array($token))
            $this->data = $token;
        else
            throw Rewriter_Exception('bad token given');
    }
    
    /**
     * Set line number.
     * 
     * @access public
     * @param int $line
     * @return int $line
     */
    public function setLine($line)
    {
        $this->data[2] = $line;
        return $line;
    }
    
    
    /**
     * Return current token name.
     * 
     * @access public
     * @return string
     */
    public function getName()
    {
        switch ($this->getToken())
        {
            case self::T_SINGLECHAR:
                return self::NAME_SINGLECHAR;
            default:
                return token_name($this->getToken());
        }
    }
    
    /**
     * Return token value.
     * 
     * @access public
     * @return int
     */
    public function getToken()
    {
        return $this->data[0];
    }
    
    /**
     * Change code of current token.
     * 
     * @access public
     * @param string $code
     * @return string $code
     */
    public function setCode($code)
    {
        $this->data[1] = (string)$code;
        return $this->data[1];
    }
    
    /**
     * Return code of this token.
     * 
     * @access public
     * @return string
     */
    public function getCode()
    {
        return $this->data[1];
    }

    /**
     * Return token line number.
     * 
     * @access public
     * @return void
     */
    public function getLine()
    {
        return $this->data[2];
    }
    
    /**
     * (string)$token give you code of this token.
     * 
     * @access public
     * @return void
     */
    public function __toString()
    {
        return $this->getCode();
    }
    
    
    /**
     * Check if this token is ';'.
     * 
     * @access public
     * @return bool
     */
    public function isSemicolon()
    {
        return ($this->getToken() == self::T_SINGLECHAR AND $this->getCode() == ';');
    }
    
    /**
     * Check if this token is '='.
     * 
     * @access public
     * @return bool
     */    
    public function isAssignment()
    {
        return ($this->getToken() == self::T_SINGLECHAR AND $this->getCode() == '=');
    }
    
    /**
     * Check if this token is '{' or '}.
     * 
     * @param bool $open. What bracket we are checking '{' or '}'.
     * @access public
     * @return bool
     */
    public function isBracket($open = true)
    {
        return ($this->getToken() == self::T_SINGLECHAR AND 
            (
                ($open AND $this->getCode() == '{') OR
                (!$open AND $this->getCode() == '}')
            )
        );
    }
    
}