<?php
namespace PMocks\Rewriter\Rule\Object;

/**
 * Rule for change constant value in the class.
 * @example $rule = \PMocks\Rewriter\Rule\Object\Constant('API_BASE_URI', 'http://example.com/api/', 'Zend_Service_Twitter');
 */
class Constant extends \PMocks\Rewriter\RuleAbstract implements \PMocks\Rewriter\RuleInterface
{
    protected
        $constantName,
        $newValue;

    
    /**
     * Create the rule.
     * 
     * @access public
     * @param string $constantName
     * @param string|number $newValue
     * @param string $className (default: null)
     */
    public function __construct($constantName, $newValue, $className = null)
    {
        $this->constantName = $constantName;
        $this->newValue = $newValue;
        if ($className)
            $this->setClass($className);
    }
    
    
    /**
     * Seek and redefine target const in the target class.
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
        if ($classParse AND $classParse[0] == '')
            array_shift($classParse);

        $targetNamespace = implode('\\', $classParse);
        
        $currentNamespace = '';
        $currentClassName = '';
        $waitForNS = $waitForClass = $waitForTargetConst = $targetConstFound = false;
        
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
            
            //found const
            if ($token->getToken() == T_CONST AND $currentNamespace == $targetNamespace AND $currentClassName == $targetClassName) {
                $waitForTargetConst = true;
                
                continue;
            }
            
            //semicolon break all waiting
            if ($token->isSemicolon()) {
                $waitForNS = $waitForClass = $waitForTargetConst = $targetConstFound = false;
                continue;
            }
            
            //replacement here
            if ($targetConstFound) {
                if ($token->isAssignment()) {
                    $targetConstFound = 2;
                    continue;
                }
                if ($targetConstFound == 2) {
                    $token->setCode($this->newValue);
                    break;
                }
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
                } elseif ($waitForTargetConst) {
                    if ($token->getCode() == $this->constantName) {
                        $targetConstFound = true;
                    } else {
                        $waitForTargetConst = false;
                    }
                }
                continue;
            }            
        }

        return $tokens;
    }
}