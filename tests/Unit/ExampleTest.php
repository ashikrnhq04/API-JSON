<?php

describe('Example Unit Test', function () {
    it('can perform basic assertions', function () {
        expect(true)->toBe(true);
    });

    it('can test mathematical operations', function () {
        expect(2 + 2)->toBe(4);
        expect(10 / 2)->toBe(5);
        expect(3 * 3)->toBe(9);
    });

    it('can test string operations', function () {
        expect('hello')->toBeString();
        expect('hello world')->toContain('world');
        expect('test')->toHaveLength(4);
    });

    it('can test array operations', function () {
        $array = [1, 2, 3, 4, 5];
        
        expect($array)->toBeArray()
            ->and($array)->toHaveCount(5)
            ->and($array)->toContain(3);
    });

    it('can test custom expectations', function () {
        expect(1)->toBeOne();
    });
});
