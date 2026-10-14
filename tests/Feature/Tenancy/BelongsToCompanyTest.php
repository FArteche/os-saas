<?php

use App\Models\Company;
use App\Tenancy\CurrentCompany;
use Tests\Fixtures\Note;

beforeEach(function () {
    Note::createTable();

    $this->shopA = Company::factory()->create();
    $this->shopB = Company::factory()->create();

    Note::create(['company_id' => $this->shopA->id, 'body' => 'A1']);
    Note::create(['company_id' => $this->shopA->id, 'body' => 'A2']);
    Note::create(['company_id' => $this->shopB->id, 'body' => 'B1']);
});

it('only returns rows of the current company', function () {
    app(CurrentCompany::class)->set($this->shopA);

    expect(Note::pluck('body')->sort()->values()->all())->toBe(['A1', 'A2'])
        ->and(Note::count())->toBe(2);
});

it('cannot reach another company row even by id', function () {
    app(CurrentCompany::class)->set($this->shopA);
    $foreign = Note::withoutCompanyScope()->where('body', 'B1')->first();

    expect(Note::find($foreign->id))->toBeNull();
});

it('returns nothing when there is no current company', function () {
    expect(Note::count())->toBe(0);
});

it('can opt out of the scope explicitly', function () {
    expect(Note::withoutCompanyScope()->count())->toBe(3);
});

it('fills company_id from the current company on create', function () {
    app(CurrentCompany::class)->set($this->shopB);

    $note = Note::create(['body' => 'B2']);

    expect($note->company_id)->toBe($this->shopB->id)
        ->and($note->company->is($this->shopB))->toBeTrue();
});

it('refuses to create a row without any company', function () {
    Note::create(['body' => 'orphan']);
})->throws(LogicException::class);
