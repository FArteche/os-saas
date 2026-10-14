<?php

namespace Tests\Fixtures;

use App\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Minimal company-owned model used to test the tenancy layer in isolation.
 *
 * @property int $id
 * @property string $body
 */
class Note extends Model
{
    use BelongsToCompany;

    protected $table = 'test_notes';

    protected $guarded = [];

    public static function createTable(): void
    {
        Schema::create('test_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('body');
            $table->timestamps();
        });
    }
}
