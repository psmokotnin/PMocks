<?php
namespace PMocks\Rewriter\Rule;

/**
 * Replace rule replaces given token for your own;
 *
 * @example $rule = PMocks\Rewriter\Rule\Replace(T_PRIVATE, 'public'); make all private methods public.
 */
class Replace extends \PMocks\Rewriter\RuleAbstract implements \PMocks\Rewriter\RuleInterface
{
    protected
        $targetToken,
        $replacement;

    /**
     * __construct function.
     * 
     * @access public
     * @param const $token Token const T_*
     * @param string $replacement
     */
    public function __construct($token, $replacement)
    {
        $this->targetToken = $token;
        $this->replacement = $replacement;
    }
    
    
    /**
     * Apply rule for code tokens.
     * 
     * @access public
     * @param array $tokens Original tokens
     * @return array Modefied tokens
     */
    public function apply($tokens)
    {
        $targetToken = $this->targetToken;
        $replacement = $this->replacement;

        array_walk($tokens, function(&$token) use ($targetToken, $replacement) {
            if ($token->getToken() == $targetToken) {
                $token->setCode($replacement);
            }
        });
        return $tokens;
    }
}