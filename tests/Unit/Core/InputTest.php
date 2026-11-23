<?php

final class InputTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->backupRequest = $_REQUEST;
        $_REQUEST = [];
    }

    private array $backupRequest;

    protected function tearDown(): void
    {
        $_REQUEST = $this->backupRequest;
        parent::tearDown();
    }

    public function test_reads_named_request_values_and_defaults(): void
    {
        $_REQUEST = ['value' => 'request', 'zero' => 0];
        $_GET = ['value' => 'query'];
        $_POST = ['value' => 'form'];
        self::assertSame('request', Input::get('value'));
        self::assertSame('query', Input::getVar('value', null, Input::__GET__));
        self::assertSame('form', Input::getVar('value', null, Input::__POST__));
        self::assertSame(0, Input::get('zero'));
        self::assertTrue(Input::has('zero'));
        self::assertFalse(Input::has('missing'));
        self::assertSame('fallback', Input::get('missing', 'fallback'));
        self::assertSame('fallback', Input::getVar('missing', 'fallback', Input::__POST__));
        self::assertSame('fallback', Input::getVar('missing', 'fallback', Input::__GET__));
    }

    public function test_merges_form_values_and_filters_selected_keys(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET = ['duplicate' => 'query', 'search' => 'fixture'];
        $_POST = ['duplicate' => 'form', 'count' => 0];

        self::assertSame(['duplicate' => 'form', 'search' => 'fixture', 'count' => 0], Input::all());
        self::assertSame(['count' => 0, 'duplicate' => 'form'], Input::only(['count', 'unknown', 'duplicate']));
        self::assertSame(['search' => 'fixture', 'count' => 0], Input::except(['duplicate', 'unknown']));

        $_SERVER['REQUEST_METHOD'] = 'GET';
        self::assertSame($_GET, Input::all());
    }
}
