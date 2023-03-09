<?php

class IndexApiController extends AbstractApiController
{
    public function index()
    {
        jsonResponse($_POST);
    }
}
