<?php

use App\Support\Database\AsView;
use Illuminate\Database\Migrations\Migration;

class CreateCountriesView extends Migration
{
    use AsView;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createView('countries');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropView('countries');
    }
}
