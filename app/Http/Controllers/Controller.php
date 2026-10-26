<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function pressingId(): int
    {
        return (int) auth()->user()->pressing_id;
    }
}
