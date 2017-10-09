<?php
declare(strict_types=1);

namespace Lassonde\Ifpa\Tests;

use PHPUnit\Framework\TestCase;
use Lassonde\Ifpa\IfpaClient;
use Lassonde\Ifpa\CurlRequestFactory;
use Lassonde\Ifpa\CurlRequest;
use Lassonde\Ifpa\Testing\FakeCurlRequest;

/**
 * Tests for IfpaClient.
 */
class IfpaClientTest extends TestCase
{
    const API_KEY = 'abc123';

    private $_curl_request_factory;
    private $_client;

    protected function setUp()
    {
        $this->_curl_request_factory
            = $this->createMock(CurlRequestFactory::class);
        $this->_client
            = new IfpaClient(
                IfpaClientTest::API_KEY, $this->_curl_request_factory
            );
    }

    /**
     * @covers IfpaClient::getPlayer
     */
    public function testGetPlayer(): void
    {
        $responseBody
            = '{"player":{"player_id":"25696","first_name":"Robin","last_name":'
                . '"Lassonde ","city":"Berkeley","state":"CA","country_code":"U'
                . 'S","country_name":"United States","initials":"RML","age":1,'
                . '"excluded_flag":"N","ifpa_registered":"Y"},"player_stats":{"'
                . 'current_wppr_rank":"81","last_month_rank":"82","last_year_ra'
                . 'nk":"85","highest_rank":"67","highest_rank_date":"2017-07-01'
                . '","current_wppr_value":"377.13","wppr_points_all_time":"942.'
                . '94","best_finish":"1","best_finish_count":"26","average_fini'
                . 'sh":"16","average_finish_last_year":"15","total_events_all_t'
                . 'ime":"164","total_active_events":"159","total_events_away":"'
                . '5","ratings_rank":"93","ratings_value":"1697.08","efficiency'
                . '_rank":"361","efficiency_value":"24.370"},"championshipSerie'
                . 's":[{"view_id":"83","group_code":"CA","group_name":"Californ'
                . 'ia","rank":"3","country_name":"US"},{"view_id":"102","group_'
                . 'code":"OK","group_name":"Oklahoma","rank":"26","country_name'
                . '":"US"},{"view_id":"84","group_code":"CO","group_name":"Colo'
                . 'rado","rank":"29","country_name":"US"},{"view_id":"110","gro'
                . 'up_code":"BC","group_name":"British Columbia","rank":"34","c'
                . 'ountry_name":"Canada"},{"view_id":"105","group_code":"TX","g'
                . 'roup_name":"Texas","rank":"38","country_name":"US"},{"view_i'
                . 'd":"104","group_code":"PA","group_name":"Pennsylvania","rank'
                . '":"85","country_name":"US"}]}';

        $this->_curl_request_factory
            ->method('newCurlRequest')
            ->willReturn(new FakeCurlRequest($responseBody, 200));

        $this->_curl_request_factory
            ->expects($this->once())
            ->method('newCurlRequest')
            ->will(
                $this->returnCallback(
                    function ($url) {
                        $this->assertEquals(
                            'https://api.ifpapinball.com/v1/player/25696'
                                .'?api_key='
                                . IfpaClientTest::API_KEY,
                            $url
                        );
                    }
                )
            );

        $result = $this->_client->getPlayer('25696');

        $this->assertEquals('25696', $result->getId());
        $this->assertEquals('Robin', $result->getFirstName());
        $this->assertEquals('Lassonde', $result->getLastName());
        $this->assertEquals('Berkeley', $result->getCity());
        $this->assertEquals('CA', $result->getState());
        $this->assertEquals('US', $result->getCountryCode());
        $this->assertEquals('United States', $result->getCountryName());
        $this->assertEquals('RML', $result->getInitials());
        $this->assertEquals(1, $result->getAge());
        $this->assertEquals(false, $result->getIsExcluded());
        $this->assertEquals(true, $result->getIsIfpaRegistered());
        $stats = $result->getStats();
        $this->assertEquals(81, $stats->getWpprRank());
        $this->assertEquals(93, $stats->getRatingRank());
        $this->assertEquals(1697.08, $stats->getRatingValue());
    }

    /**
     * @covers IfpaClient::getPlayer
     */
    public function testGetPlayer_nonexistantId(): void
    {
        $responseBody
            = '{"player":{"player_id":null,"first_name":null,"last_name":null,"'
                . 'city":null,"state":null,"country_code":null,"country_name":n'
                . 'ull,"initials":null,"age":47,"excluded_flag":null,"ifpa_regi'
                . 'stered":"N"},"player_stats":{"current_wppr_rank":null,"last_'
                . 'month_rank":null,"last_year_rank":null,"highest_rank":null,"'
                . 'highest_rank_date":null,"current_wppr_value":null,"wppr_poin'
                . 'ts_all_time":null,"best_finish":null,"best_finish_count":nul'
                . 'l,"average_finish":null,"average_finish_last_year":null,"tot'
                . 'al_events_all_time":null,"total_active_events":null,"total_e'
                . 'vents_away":null,"ratings_rank":"Not Ranked","ratings_value"'
                . ':"","efficiency_rank":"Not Ranked","efficiency_value":""}}';
        $this->_curl_request_factory
            ->method('newCurlRequest')
            ->willReturn(new FakeCurlRequest($responseBody, 200));

        try {
            $this->_client->getPlayer('99999999');
            $this->fail('Expected IfpaDataException to be thrown');
        } catch (\Lassonde\Ifpa\IfpaDataException $e) {
            $this->assertEquals(null, $e->getData()['player']['player_id']);
        }
    }

    /**
     * @covers IfpaClient::getPlayer
     */
    public function testGetPlayer_allowsEmptyValues(): void
    {
        // NOTE: observed that IFPA gives "" for missing age. Haven't yet
        // confirmed values for other missing keys for a legit player.
        $responseBody
            = '{"player":{"player_id":"99999999","first_name":null,"last_name":'
                . 'null,"city":null,"state":null,"country_code":null,"country_n'
                . 'ame":null,"initials":null,"age":"","excluded_flag":null,"ifp'
                . 'a_registered":"N"},"player_stats":{"current_wppr_rank":null,'
                . '"last_month_rank":null,"last_year_rank":null,"highest_rank":'
                . 'null,"highest_rank_date":null,"current_wppr_value":null,"wpp'
                . 'r_points_all_time":null,"best_finish":null,"best_finish_coun'
                . 't":null,"average_finish":null,"average_finish_last_year":nul'
                . 'l,"total_events_all_time":null,"total_active_events":null,"t'
                . 'otal_events_away":null,"ratings_rank":"Not Ranked","ratings_'
                . 'value":"","efficiency_rank":"Not Ranked","efficiency_value":'
                . '""}}';

        $this->_curl_request_factory
            ->method('newCurlRequest')
            ->willReturn(new FakeCurlRequest($responseBody, 200));

        $result = $this->_client->getPlayer('99999999');

        $this->assertEquals('99999999', $result->getId());
        $this->assertEquals(null, $result->getFirstName());
        $this->assertEquals(null, $result->getLastName());
        $this->assertEquals(null, $result->getCity());
        $this->assertEquals(null, $result->getState());
        $this->assertEquals(null, $result->getCountryCode());
        $this->assertEquals(null, $result->getCountryName());
        $this->assertEquals(null, $result->getInitials());
        $this->assertEquals(null, $result->getAge());
        $this->assertEquals(null, $result->getIsExcluded());
        $this->assertEquals(null, $result->getIsIfpaRegistered());
        $stats = $result->getStats();
        $this->assertEquals($stats->getWpprRank(), null);
        $this->assertEquals($stats->getRatingRank(), null);
        $this->assertEquals($stats->getRatingValue(), null);
    }

    /**
     * @covers IfpaClient::listPlayerIdsByNameSegment
     */
    public function testListPlayerIdsByNameSegment(): void
    {
        $responseBody
            = '{"query":"sonde","search":[{"player_id":"25696","first_name":"Ro'
                . 'bin","last_name":"Lassonde ","country_code":"US","country_na'
                . 'me":"United States","city":"Berkeley","state":"CA","wppr_ran'
                . 'k":"81"},{"player_id":"9303","first_name":"Jason","last_name'
                . '":"Delano","country_code":"US","country_name":"United States'
                . '","city":"","state":"","wppr_rank":"4313"},{"player_id":"558'
                . '48","first_name":"Jayson","last_name":"Delorme ","country_co'
                . 'de":"CA","country_name":"Canada","city":"","state":"","wppr_'
                . 'rank":"14667"},{"player_id":"39134","first_name":"Jason","la'
                . 'st_name":"Detloff ","country_code":"US","country_name":"Unit'
                . 'ed States","city":"","state":"","wppr_rank":"24236"},{"playe'
                . 'r_id":"43401","first_name":"Jason","last_name":"Dellamater "'
                . ',"country_code":"US","country_name":"United States","city":"'
                . '","state":"","wppr_rank":"30329"},{"player_id":"41158","firs'
                . 't_name":"Jason","last_name":"decou ","country_code":"US","co'
                . 'untry_name":"United States","city":"","state":"","wppr_rank"'
                . ':"32640"}]}';
        $this->_curl_request_factory
            ->method('newCurlRequest')
            ->willReturn(new FakeCurlRequest($responseBody, 200));

        $this->_curl_request_factory
            ->expects($this->once())
            ->method('newCurlRequest')
            ->will(
                $this->returnCallback(
                    function ($url) {
                        $this->assertEquals(
                            'https://api.ifpapinball.com/v1/player/search'
                                . '?api_key='
                                . IfpaClientTest::API_KEY
                                . '&q=sonde',
                            $url
                        );
                    }
                )
            );

        $result = $this->_client->listPlayerIdsByNameSegment('sonde');

        $this->assertEquals(6, count($result));
        $this->assertEquals('25696', $result[0]);
    }

    /**
     * @covers IfpaClient::listPlayerIdsByNameSegment
     */
    public function testListPlayerIdsByNameSegment_emptyList(): void
    {
        $this->_curl_request_factory
            ->method('newCurlRequest')
            ->willReturn(
                new FakeCurlRequest(
                    '{"query":"zonde","search":"No players found"}',
                    200
                )
            );

        $result = $this->_client->listPlayerIdsByNameSegment('zonde');

        $this->assertEquals(0, count($result));
    }

    /**
     * @covers IfpaClient::listPlayerIdsByEmail
     */
    public function testListPlayerIdsByEmail(): void
    {
        $responseBody
            = '{"query":"rlassonde@gmail.com","search":[{"player_id":"25696","f'
                . 'irst_name":"Robin","last_name":"Lassonde ","country_code":"U'
                . 'S","country_name":"United States","city":"Berkeley","state":'
                . '"CA","wppr_rank":"81"}]}';
        $this->_curl_request_factory
            ->method('newCurlRequest')
            ->willReturn(new FakeCurlRequest($responseBody, 200));

        $this->_curl_request_factory
            ->expects($this->once())
            ->method('newCurlRequest')
            ->will(
                $this->returnCallback(
                    function ($url) {
                        $this->assertEquals(
                            'https://api.ifpapinball.com/v1/player/search'
                                . '?api_key='
                                . IfpaClientTest::API_KEY
                                . '&email=rlassonde%40gmail.com',
                            $url
                        );
                    }
                )
            );

        $result = $this->_client->listPlayerIdsByEmail('rlassonde@gmail.com');

        $this->assertEquals(1, count($result));
        $this->assertEquals('25696', $result[0]);
    }

    /**
     * @covers IfpaClient::listPlayerIdsByEmail
     */
    public function testListPlayerIdsByEmail__emptyList(): void
    {
        $this->_curl_request_factory
            ->method('newCurlRequest')
            ->willReturn(
                new FakeCurlRequest(
                    '{"query":"foo@bar.com","search":"No players found"}',
                    200
                )
            );

        $result = $this->_client->listPlayerIdsByEmail('foo@bar.com');

        $this->assertEquals(0, count($result));
    }

    public function testSetTimeoutSeconds(): void
    {
        $this->_client->setTimeoutSeconds(4);
        $curlRequest
            = new FakeCurlRequest('{"player":{"player_id":"25696"}}', 200);
        $this->_curl_request_factory
            ->method('newCurlRequest')
            ->willReturn($curlRequest);

        $this->_client->getPlayer('25696');

        $this->assertEquals(4, $curlRequest->getOption(CURLOPT_CONNECTTIMEOUT));
    }
}
