<?php

declare(strict_types=1);

namespace JWilsson;

it('should set the threshold option passed', function () {
    $options = new Options([
        'threshold' => 600,
    ]);

    expect($options->threshold)->toBe(600);
});

it('should throw when passed an invalid threshold', function(){
    expect(function () {
        new Options([
            'threshold' => 'invalid',
        ]);
    })->toThrow(\InvalidArgumentException::class);
});
