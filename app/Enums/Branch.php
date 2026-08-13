<?php

namespace App\Enums;

enum Branch: string
{
    case CHROMEPET = 'chromepet';
    case ORAGADAM = 'oragadam';

    /**
     * Create an instance from a string value.
     *
     * @param string $value
     * @return self
     */
    public static function fromString(string $value): self
    {
        return self::from($value);
    }

    /**
     * Get the human-readable label for the branch.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::CHROMEPET => 'Chromepet',
            self::ORAGADAM => 'Oragadam',
        };
    }

    /**
     * Get the total bed count for the branch.
     *
     * @return int
     */
    public function bedCount(): int
    {
        return match($this) {
            self::CHROMEPET => 74,
            self::ORAGADAM => 14,
        };
    }
}
