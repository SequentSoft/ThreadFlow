<?php

use SequentSoft\ThreadFlow\Session\Session;

it('gets data', function () {
    $data = ['key' => 'value'];
    $session = new Session($data, function () {
    });

    $this->assertEquals($data, $session->getData());
});

it('deletes data', function () {
    $data = ['key' => 'value'];
    $session = new Session($data, function () {
    });

    $session->delete('key');

    $this->assertEquals([], $session->getData());
});

it('throws when deleting data from a closed session', function () {
    $data = ['key' => 'value'];
    $session = new Session($data, function () {
    });

    $session->close();
    $session->delete('key'); // This should throw a RuntimeException
})->throws(RuntimeException::class);

it('gets value by key with default', function () {
    $data = ['key' => 'value'];
    $session = new Session($data, function () {
    });

    $this->assertEquals('value', $session->get('key'));
    $this->assertEquals('default', $session->get('nonexistent', 'default'));
});

it('sets data', function () {
    $session = new Session([], function () {
    });

    $session->set('key', 'value');

    $this->assertEquals(['key' => 'value'], $session->getData());
});

it('throws when setting data to a closed session', function () {
    $session = new Session([], function () {
    });

    $session->close();
    $session->set('key', 'value'); // This should throw a RuntimeException
})->throws(RuntimeException::class);

it('creates a nested session', function () {
    $session = new Session([], function () {
    });

    $nested = $session->nested('key');
    $nested->set('nestedKey', 'nestedValue');
    $nested->close(); // Close the nested session to ensure its data is saved to the parent session.

    $this->assertEquals(['key' => ['nestedKey' => 'nestedValue']], $session->getData());
});

it('closes the session', function () {
    $session = new Session(['key' => 'value'], function () {
    });

    $session->close();
    $this->assertTrue(true); // If we've reached this point, then the test passed.
});
