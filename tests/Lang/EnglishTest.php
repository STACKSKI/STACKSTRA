<?php

namespace Stackstra\Tests\Lang;

use PHPUnit\Framework\Attributes\CoversClass;
use Stackstra\Lang\English;
use Stackstra\Tests\TestCase;

#[CoversClass(English::class)]
class EnglishTest extends TestCase
{
    public function testPluralize(): void
    {
        // uncountable: returned unchanged
        $this->assertSame('sheep', English::pluralize('sheep'));
        $this->assertSame('fish', English::pluralize('fish'));

        // irregular
        $this->assertSame('men', English::pluralize('man'));
        $this->assertSame('children', English::pluralize('child'));
        $this->assertSame('people', English::pluralize('person'));

        // regular regex rules
        $this->assertSame('quizzes', English::pluralize('quiz'));
        $this->assertSame('oxen', English::pluralize('ox'));
        $this->assertSame('mice', English::pluralize('mouse'));
        $this->assertSame('matrices', English::pluralize('matrix'));
        $this->assertSame('boxes', English::pluralize('box'));
        $this->assertSame('cities', English::pluralize('city'));
        $this->assertSame('hives', English::pluralize('hive'));
        $this->assertSame('wolves', English::pluralize('wolf'));
        $this->assertSame('leaves', English::pluralize('leaf'));
        $this->assertSame('diagnoses', English::pluralize('diagnosis'));
        $this->assertSame('data', English::pluralize('datum'));
        $this->assertSame('potatoes', English::pluralize('potato'));
        $this->assertSame('buses', English::pluralize('bus'));
        $this->assertSame('aliases', English::pluralize('alias'));
        $this->assertSame('octopi', English::pluralize('octopus'));
        $this->assertSame('axes', English::pluralize('axis'));
        $this->assertSame('viruses', English::pluralize('virus'));

        // fallback: no rule matches beyond appending 's'
        $this->assertSame('cats', English::pluralize('cat'));

        // already plural (ends with 's'): the '/s$/i' => 's' rule is a no-op
        $this->assertSame('cats', English::pluralize('cats'));
    }

    public function testSingularize(): void
    {
        // uncountable: returned unchanged
        $this->assertSame('sheep', English::singularize('sheep'));

        // irregular
        $this->assertSame('man', English::singularize('men'));
        $this->assertSame('child', English::singularize('children'));
        $this->assertSame('person', English::singularize('people'));

        // regular regex rules
        $this->assertSame('quiz', English::singularize('quizzes'));
        $this->assertSame('matrix', English::singularize('matrices'));
        $this->assertSame('ox', English::singularize('oxen'));
        $this->assertSame('alias', English::singularize('aliases'));
        $this->assertSame('octopus', English::singularize('octopi'));
        $this->assertSame('box', English::singularize('boxes'));
        $this->assertSame('city', English::singularize('cities'));
        $this->assertSame('wolf', English::singularize('wolves'));
        $this->assertSame('diagnosis', English::singularize('diagnoses'));
        $this->assertSame('datum', English::singularize('data'));
        $this->assertSame('bus', English::singularize('buses'));

        // fallback: strip trailing 's'
        $this->assertSame('cat', English::singularize('cats'));

        // already singular (no trailing 's'): unchanged
        $this->assertSame('cat', English::singularize('cat'));
    }

    public function testNumber(): void
    {
        // non-numeric input: empty string
        $this->assertSame('', English::number('not a number'));

        // negative numbers: prefixed and recursed on the absolute value
        $this->assertSame('negative five', English::number(-5));

        // 0-20: direct dictionary lookup
        $this->assertSame('zero', English::number(0));
        $this->assertSame('one', English::number(1));
        $this->assertSame('twenty', English::number(20));

        // 21-99: tens + hyphen + units, or just tens if evenly divisible
        $this->assertSame('twenty-one', English::number(21));
        $this->assertSame('thirty', English::number(30));
        $this->assertSame('ninety-nine', English::number(99));

        // 100-999: hundreds + optional conjunction + remainder
        $this->assertSame('one hundred', English::number(100));
        $this->assertSame('one hundred and one', English::number(101));
        $this->assertSame('nine hundred and ninety-nine', English::number(999));

        // 1000+: base unit word + optional separator/conjunction + remainder
        $this->assertSame('one thousand', English::number(1000));
        $this->assertSame('one thousand and one', English::number(1001));
        $this->assertSame('one thousand, one hundred', English::number(1100));
        $this->assertSame('one million', English::number(1000000));

        // custom hyphen/conjunction/separator/negative arguments
        $this->assertSame('twenty_one', English::number(21, '_'));
        $this->assertSame('one hundred - one', English::number(101, '-', ' - '));
        $this->assertSame('one thousand; one hundred', English::number(1100, '-', ' and ', '; '));
        $this->assertSame('MINUS five', English::number(-5, '-', ' and ', ', ', 'MINUS '));

        // decimal input: fraction digits spelled out one at a time after "point"
        $this->assertSame('one point five', English::number(1.5));
        $this->assertSame('one point two five', English::number('1.25'));
        $this->assertSame('one point five', English::number(1.5, '-', ' and ', ', ', 'negative ', ' point '));

        // custom decimal separator
        $this->assertSame('one comma five', English::number(1.5, decimal: ' comma '));
    }
}
