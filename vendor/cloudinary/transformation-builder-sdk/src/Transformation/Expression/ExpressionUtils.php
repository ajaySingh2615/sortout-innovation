<?php
/**
 * This file is part of the Cloudinary PHP package.
 *
 * (c) Cloudinary
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cloudinary\Transformation\Expression;

use Cloudinary\TransformationUtils;

/**
 * Class ExpressionUtils
 *
 * @internal
 */
class ExpressionUtils
{
    /**
     * @var array $OPERATORS A list of supported operators (arithmetic, logical, relational).
     */
    private static array $OPERATORS;

    /**
     * @var array $PREDEFINED_VARIABLES A list of supported predefined variables.
     */
    private static array $PREDEFINED_VARIABLES;

    /**
     * @var string $IF_REPLACE_RE Operators and predefined variables serialisation regular expression.
     *
     * @see ExpressionUtils::lazyInit
     */
    private static string $IF_REPLACE_RE;

    /**
     * Normalizes expression from user representation to URL form.
     *
     * @param mixed $expression The expression to normalize.
     *
     * @return ?string The normalized expression.
     *
     * @uses translateIf()
     *
     */
    public static function normalize(mixed $expression): ?string
    {
        if ($expression === null || self::isLiteral($expression)) {
            return $expression;
        }

        if (is_float($expression)) {
            return TransformationUtils::floatToString($expression);
        }

        self::lazyInit();

        $expression = preg_replace('/[ _]+/', '_', $expression);

        return preg_replace_callback(
            self::$IF_REPLACE_RE,
            static fn(array $source) => self::translateIf($source),
            $expression
        );
    }

    /**
     * Initializes ExpressionUtils::$IF_REPLACE_RE static member lazily
     *
     * @see ExpressionUtils::$IF_REPLACE_RE
     */
    private static function lazyInit(): void
    {
        if (! empty(self::$IF_REPLACE_RE)) {
            return; //initialized last, if initialized, all the rest is OK
        }

        if (empty(self::$OPERATORS)) {
            self::$OPERATORS = Operator::friendlyRepresentations();
        }

        if (empty(self::$PREDEFINED_VARIABLES)) {
            self::$PREDEFINED_VARIABLES = PVar::getFriendlyRepresentations();
        }

        if (empty(self::$IF_REPLACE_RE)) {
            self::$IF_REPLACE_RE = '/((\$_*[^_]+)|(\|\||>=|<=|&&|!=|>|=|<|\/|\-|\+|\*|\^)(?=[ _])|(?<![\$\:])(' .
                                   implode('|', array_keys(self::$PREDEFINED_VARIABLES)) .
                                   '))/';
        }
    }

    /**
     * Serializes operators and predefined variables to url representation.
     *
     * @callable
     *
     * @param array $source The source to translate.
     *
     *
     * @see normalize()
     */
    protected static function translateIf(array $source): mixed
    {
        if (isset(self::$OPERATORS[$source[0]])) {
            return self::$OPERATORS[$source[0]];
        }

        if (isset(self::$PREDEFINED_VARIABLES[$source[0]])) {
            return self::$PREDEFINED_VARIABLES[$source[0]];
        }

        return $source[0];
    }

    /**
     * Indicates whether $expression is a literal string (surrounded by '!')
     *
     * @param string $expression The expression
     *
     */
    protected static function isLiteral(string $expression): bool
    {
        return (boolean)preg_match('/^!.+!$/', $expression);
    }
}
