<?php
declare(strict_types=1);

namespace Lassonde\Ifpa;

/**
 * Class for making requests to the IFPA api and returning the results as nice
 * objects.
 */
class IfpaClient
{
    private $_http_caller;

    public function __construct(
        string $api_key, CurlRequestFactory $curl_request_factory
    ) {
        $this->_http_caller
            = new IfpaHttpCaller($api_key, $curl_request_factory);
    }

    /**
     * Sets the request timeout, in seconds.
     */
    public function setTimeoutSeconds(int $timeout_seconds)
    {
        $this->_http_caller->setTimeoutSeconds($timeout_seconds);
    }

    /**
     * Get information about player with the given id.
     *
     * Automatically trims whitespace around string values, since IFPA records
     *     have trailing whitespace for many players' last names.
     *
     * @throws IfpaHttpException if the http status code is anything other than
     *     200.
     * @throws IfpaDataException if the response from IFPA doesn't contain the
     *     data needed to generate a Player. Note that this includes the case of
     *     non-existend ids, since the IFPA returns empty data instead of
     *     404ing.
     *
     * @see https://www.ifpapinball.com/api/documentation/player/
     */
    public function getPlayer(string $id): Player
    {
        $result = $this->_http_caller->get("player/$id");
        if (!array_key_exists('player', $result)) {
            throw new IfpaDataException('Player key missing.', $result);
        }
        $player = $result['player'];
        if (empty($player)
            || !array_key_exists('player_id', $player)
            || empty($player['player_id'])
        ) {
            throw new IfpaDataException('Player id missing.', $result);
        }
        return new Player(
            $this->_getStringOrNull('player_id', $player),
            $this->_getStringOrNull('first_name', $player),
            $this->_getStringOrNull('last_name', $player),
            $this->_getStringOrNull('city', $player),
            $this->_getStringOrNull('state', $player),
            $this->_getStringOrNull('country_code', $player),
            $this->_getStringOrNull('country_name', $player),
            $this->_getStringOrNull('initials', $player),
            // IFPA seems to return present age as int, absent age as empty
            // string
            $this->_getIntOrNull('age', $player),
            $this->_getBooleanFromFlagOrNull('excluded_flag', $player),
            $this->_getBooleanFromFlagOrNull('ifpa_registered', $player),
            (array_key_exists('player_stats', $result))
                ? $this->_getPlayerStats($result['player_stats']) : null
        );
    }

    /**
     * Returns list of ids for players whose name matches the given segment.
     *
     * @throws IfpaHttpException if the http status code is anything other than
     *     200.
     * @throws IfpaDataException if the response from IFPA doesn't contain the
     *     data needed to generate the list of ids.
     *
     * @see https://www.ifpapinball.com/api/documentation/player/
     */
    public function listPlayerIdsByNameSegment(string $name_segment): array
    {
        return $this->_getPlayerIdList(
            $this
                ->_http_caller
                ->get('player/search', array('q' => $name_segment))
        );
    }

    /**
     * Returns list of ids for players with the given email address.
     *
     * @throws IfpaHttpException if the http status code is anything other than
     *     200.
     * @throws IfpaDataException if the response from IFPA doesn't contain the
     *     data needed to generate the list of ids.
     *
     * @see https://www.ifpapinball.com/api/documentation/player/
     */
    public function listPlayerIdsByEmail(string $email): array
    {
        return $this->_getPlayerIdList(
            $this->_http_caller->get('player/search', array('email' => $email))
        );
    }

    private function _getStringOrNull(string $key, array $array): ?string
    {
        if (array_key_exists($key, $array)) {
            $value = $array[$key];
            if (is_string($value)) {
                return trim($value);
            }
        }
        return null;
    }

    private function _getIntOrNull(string $key, array $array): ?int
    {
        if (array_key_exists($key, $array)) {
            $value = $array[$key];
            if (is_int($value)) {
                return $value;
            }
        }
        return null;
    }

    private function _getFloatOrNull(string $key, array $array): ?float
    {
        if (array_key_exists($key, $array)) {
            $value = $array[$key];
            if (is_float($value)) {
                return $value;
            }
        }
        return null;
    }

    private function _getBooleanFromFlagOrNull(string $key, array $array): ?bool
    {
        if (array_key_exists($key, $array)) {
            return $array[$key] == 'Y';
        }
        return null;
    }

    private function _getPlayerStats(array $array): PlayerStats
    {
        $wppr_rank = $this->_getStringOrNull('current_wppr_rank', $array);
        $rating_rank = $this->_getStringOrNull('ratings_rank', $array);
        $rating_value = $this->_getStringOrNull('ratings_value', $array);
        return new PlayerStats(
            ($wppr_rank != null) ? intval($wppr_rank) : null,
            ($rating_rank != null) ? intval($rating_rank) : null,
            ($rating_value != null) ? floatval($rating_value) : null
        );
    }

    private function _getPlayerIdList($result): array
    {
        if (!array_key_exists('search', $result)) {
            throw new IfpaDataException('Search key missing.', $result);
        }
        $search = $result['search'];
        $id_list = array();
        if (!is_array($search)) {
            // Value seems to be string "No players found" when there are 0
            // search results.
            return $id_list;
        }
        foreach ($search as $player) {
            if (!array_key_exists('player_id', $player)) {
                throw new IfpaDataException(
                    'Search result missing player id.', $result
                );
            }
            $player_id = $player['player_id'];
            if (!is_string($player_id)) {
                throw new IfpaDataException(
                    'Search result has unexpected player id.', $result
                );
            }
            $id_list[] = $player_id;
        }

        return $id_list;
    }
}
