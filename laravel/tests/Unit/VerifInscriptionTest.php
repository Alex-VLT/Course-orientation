<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\VerifInscription;

class VerifInscriptionTest extends TestCase
{
    /** @test */
    public function get_age_at_date_returns_correct_age_on_birthday()
    {
        $age = VerifInscription::getAgeAtDate('2000-01-01', '2020-01-01');
        $this->assertSame(20, $age);
    }

    /** @test */
    public function get_age_at_date_returns_one_less_before_birthday()
    {
        $age = VerifInscription::getAgeAtDate('2000-06-15', '2020-06-14');
        $this->assertSame(19, $age);
    }

    /** @test */
    public function get_age_at_date_handles_leap_day_birthdays()
    {
        // Born on 2004-02-29: on 2021-02-28 the birthday has not yet occurred -> 16
        $age = VerifInscription::getAgeAtDate('2004-02-29', '2021-02-28');
        $this->assertSame(16, $age);

        // On 2021-03-01 the birthday is considered passed -> 17
        $age2 = VerifInscription::getAgeAtDate('2004-02-29', '2021-03-01');
        $this->assertSame(17, $age2);
    }

    /** @test */
    public function get_age_at_date_returns_null_for_invalid_inputs()
    {
        $this->assertNull(VerifInscription::getAgeAtDate('invalid-date', '2020-01-01'));
        $this->assertNull(VerifInscription::getAgeAtDate('2000-01-01', 'not-a-date'));
        $this->assertNull(VerifInscription::getAgeAtDate('', ''));
    }

    /** @test */
    public function get_age_at_date_returns_null_when_birth_is_in_future()
    {
        // Birth is after reference date -> invalid
        $this->assertNull(VerifInscription::getAgeAtDate('2030-01-01', '2025-01-01'));
    }

    /** @test */
    public function get_age_at_date_returns_zero_when_same_day()
    {
        $this->assertSame(0, VerifInscription::getAgeAtDate('2000-01-01', '2000-01-01'));
    }

    /** @test */
    public function get_age_at_date_accepts_various_date_formats()
    {
        // Different valid formats should be parsed correctly
        $this->assertSame(20, VerifInscription::getAgeAtDate('2000-1-1', '2020-01-01'));
        $this->assertSame(20, VerifInscription::getAgeAtDate('2000/01/01', '2020-01-01'));
        $this->assertSame(20, VerifInscription::getAgeAtDate('2000-01-01 10:30:00', '2020-01-01 09:00:00'));
    }

    /** @test */
    public function get_age_at_date_handles_very_old_birthdates()
    {
        // Ensure very large ages don't break the method
        $age = VerifInscription::getAgeAtDate('1900-01-01', '2000-01-01');
        $this->assertSame(100, $age);
    }
}
