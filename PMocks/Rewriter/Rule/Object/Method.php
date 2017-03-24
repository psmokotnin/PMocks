<?php
namespace PMocks\Rewriter\Rule\Object;

/**
 * Rule for change method implementation.
 * 
 * @example $rule = new PMocks\Rewriter\Rule\Object\Method('send', 'return true;', true, 'Zend_Cache_Core')
 * Each call of Zend_Cache_Core::send will add data to PMocks\Log and retun true.
 * If $className not set, it will be set in \PMocks\Loader::mockClass.
 */
class Method extends \PMocks\Rewriter\RuleAbstract implements \PMocks\Rewriter\RuleInterface
{
    protected
        $functionName,
        $insertedCode,
        $callLog;

    
    /**
     * Create the rule.
     * 
     * @access public
     * @param string $constantName
     * @param string $insertedCode
     * @param string $className (default: null)
     */
    public function __construct($functionName, $insertedCode, $callLog = false, $className = null)
    {
        $this->functionName = $functionName;
        $this->insertedCode = $insertedCode;
        $this->callLog      = $callLog;
        if ($className)
            $this->setClass($className);
    }
    
    
    /**
     * remove all new lines from inserted code.
     * Use it if your code has more than 1 lines and you want to save original line numbers.
     *
     * $code = 'for ($i = 0; $i < 10; $i ++) {
     *     someFunc($i);
     * }';
     * $rule = new Rewriter\Rule\Object\Method('foo', $code);
     * $rule->makeCodeSingleLine();
     * 
     * @access public
     * @return void
     */
    public function makeCodeSingleLine() {
        $this->insertedCode = str_replace(PHP_EOL, '', $this->insertedCode);
        return $this;
    }
    
    
    /**
     * Seek and redefine target method in the target class.
     * 
     * @access public
     * @param array $tokens
     * @return modified tokens array
     */
    public function apply($tokens)
    {
        $classParse = explode('\\', $this->getClass());
        $targetClassName = end($classParse);
        array_pop($classParse);
        if ($classParse[0] == '')
            array_shift($classParse);

        $targetNamespace = implode('\\', $classParse);
        
        $currentNamespace = '';
        $currentClassName = '';
        $waitForNS = $waitForClass = $waitForTargetFunction = $targetFunctionFound = false;
        $open = $close = null;
        $level = 0;

        foreach ($tokens as $token) {
            
            if ($token->getToken() == T_WHITESPACE)
                continue;

            //apply changes only in needed namespace
            if ($token->getToken() == T_NAMESPACE) {
                $waitForNS = true;
                $currentNamespace = '';
                continue;
            }
            
            //apply changes only in needed class
            if ($token->getToken() == T_CLASS AND $currentNamespace == $targetNamespace) {
                $waitForClass = true;
                $currentClassName = '';
                continue;
            }
            
            //found function
            if ($token->getToken() == T_FUNCTION AND $currentNamespace == $targetNamespace AND $currentClassName == $targetClassName) {
                $waitForTargetFunction = true;
                
                continue;
            }
            
            //semicolon break ns waiting
            if ($token->isSemicolon() AND $waitForNS) {
                $waitForNS = false;
                continue;
            }
            
            //check for needed T_STRING
            if ($token->getToken() == T_STRING) {
                if ($waitForNS) {
                    $currentNamespace = $token->getCode();
                    continue;
                } elseif ($waitForClass) {
                    $currentClassName = $token->getCode();
                    $waitForClass = false;
                    continue;
                } elseif ($waitForTargetFunction) {
                    if ($token->getCode() == $this->functionName) {
                        $targetFunctionFound = true;
                    } else {
                        $waitForTargetFunction = false;
                    }
                    
                    $waitForTargetFunction = false;
                }
                continue;
            }
            
            if ($token->isBracket() AND $targetFunctionFound) {
                $token->setCode(
                    '{' . 
                    ($this->callLog ? '\PMocks\Log::add(debug_backtrace());' : '') .
                    $this->insertedCode
                );
                break;
            }
        }

        return $tokens;
    }
}