<?php
declare(strict_types=1);

namespace Lassonde\Ifpa;

/**
 * Object to hold data about a player's statistics.
 */
class PlayerStats
{
    private $_wppr_rank;
    private $_rating_rank;
    private $_rating_value;

    public function __construct(
        ?int $wppr_rank, ?int $rating_rank, ?float $rating_value
    ) {
        $this->_wppr_rank = $wppr_rank;
        $this->_rating_rank = $rating_rank;
        $this->_rating_value = $rating_value;

    }
    public function getWpprRank(): ?int
    {
        return $this->_wppr_rank;
    }

    public function getRatingRank(): ?int
    {
        return $this->_rating_rank;
    }

    public function getRatingValue(): ?float
    {
        return $this->_rating_value;
    }
}
