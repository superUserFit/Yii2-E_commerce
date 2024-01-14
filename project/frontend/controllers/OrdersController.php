<?php

namespace frontend\controllers;

class OrdersController extends \frontend\base\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

}
