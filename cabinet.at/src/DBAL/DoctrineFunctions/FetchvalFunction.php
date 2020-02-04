<?php
declare(strict_types=1);

namespace App\DBAL\DoctrineFunctions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;

class FetchvalFunction extends FunctionNode
{
    public $hstoreExpression = null;
    public $keyExpression = null;

    /**
     * @param SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return 'fetchval(' .
            $this->hstoreExpression->dispatch($sqlWalker) . ', ' .
            $this->keyExpression->dispatch($sqlWalker) . ')';
    }

    /**
     * @param Parser $parser
     * @throws QueryException
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->hstoreExpression = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->keyExpression = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}