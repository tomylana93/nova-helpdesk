<?php

namespace App\Concerns;

use LogicException;
use UnexpectedValueException;

trait HasOptions
{
    /**
     * Get the enum cases as options.
     *
     * @param  array<string, string>  $additionalFields
     * @return list<array<string, string>>
     */
    public static function options(array $additionalFields = []): array
    {
        return array_map(
            fn (self $case): array => self::optionForCase($case, $additionalFields),
            self::cases(),
        );
    }

    /**
     * @param  array<string, string>  $additionalFields
     * @return array<string, string>
     */
    private static function optionForCase(self $case, array $additionalFields): array
    {
        $option = [
            'label' => $case->label(),
            'value' => $case->value,
        ];

        foreach ($additionalFields as $field => $method) {
            if (! method_exists($case, $method)) {
                throw new LogicException(sprintf(
                    'Method [%s] does not exist on enum [%s].',
                    $method,
                    $case::class,
                ));
            }

            $value = $case->{$method}();
            if (! is_string($value)) {
                throw new UnexpectedValueException(sprintf(
                    'Method [%s] on enum [%s] must return a string option field.',
                    $method,
                    $case::class,
                ));
            }

            $option[$field] = $value;
        }

        return $option;
    }
}
