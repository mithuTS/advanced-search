<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class CustomerController extends VoyagerBaseController
{
    /**
     * Make customer module
     * advanced searchable
     * CustomerController constructor.
     */
    public function __construct() {
        $this->setAdvSearch(true);
    }
}
