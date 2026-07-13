<?php

test('the health check endpoint returns a successful response', function () {
    $response = $this->get('/api/v1/health');

    $response->assertStatus(200)->assertJson([
        'data' => ['status' => 'ok'],
    ]);
});
