<?php
declare(strict_types=1);

namespace Lassonde\Ifpa;

/**
 * Object to hold data about a player.
 */
class Player
{
    private $_id;
    private $_first_name;
    private $_last_name;
    private $_city;
    private $_state;
    private $_country_code;
    private $_country_name;
    private $_initials;
    private $_age;
    private $_is_excluded;
    private $_is_ifpa_registered;
    private $_stats;

    public function __construct(
        string $id,
        ?string $first_name,
        ?string $last_name,
        ?string $city,
        ?string $state,
        ?string $country_code,
        ?string $country_name,
        ?string $initials,
        ?int $age,
        ?bool $is_excluded,
        ?bool $is_ifpa_registered,
        ?PlayerStats $stats
    ) {
        $this->_id = $id;
        $this->_first_name = $first_name;
        $this->_last_name = $last_name;
        $this->_city = $city;
        $this->_state = $state;
        $this->_country_code = $country_code;
        $this->_country_name = $country_name;
        $this->_initials = $initials;
        $this->_age = $age;
        $this->_is_excluded = $is_excluded;
        $this->_is_ifpa_registered = $is_ifpa_registered;
        $this->_stats = $stats;
    }

    public function getId(): string
    {
        return $this->_id;
    }

    public function getFirstName(): ?string
    {
        return $this->_first_name;
    }

    public function getLastName(): ?string
    {
        return $this->_last_name;
    }

    public function getCity(): ?string
    {
        return $this->_city;
    }

    public function getState(): ?string
    {
        return $this->_state;
    }

    public function getCountryCode(): ?string
    {
        return $this->_country_code;
    }

    public function getCountryName(): ?string
    {
        return $this->_country_name;
    }

    public function getInitials(): ?string
    {
        return $this->_initials;
    }

    public function getAge(): ?int
    {
        return $this->_age;
    }

    public function getIsExcluded(): ?bool
    {
        return $this->_is_excluded;
    }

    public function getIsIfpaRegistered(): ?bool
    {
        return $this->_is_ifpa_registered;
    }

    public function getStats(): ?PlayerStats
    {
        return $this->_stats;
    }
}
