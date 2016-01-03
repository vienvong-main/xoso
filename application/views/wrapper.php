<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="/assets/css/bootstrap-theme.min.css"/>
    <link rel="stylesheet" href="/assets/css/main.css"/>
    <link type="text/css" rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto+Condensed:400,700&subset=vietnamese,latin,latin-ext">
    <title>Xổ số</title>
</head>

<body ng-app="xosoApp">

<div class="body-wrapper">
    <div class="container"></div>

    <?php $this->load->view('menu');?>

    <a class="back-to-top" href="#">
        <img src="/assets/img/jumptop.png" alt="Back to Top"/>
    </a>

    <?php $this->load->view('templates/' . $content_view);?>

    <?php $this->load->view('footer');?>

    <script src="/assets/js/jquery-2.1.4.min.js"></script>
    <script src="/assets/js/bootstrap.min.js"></script>
    <script src="/assets/js/angular.min.js"></script>
    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/xoso.js"></script>
    <script src="/assets/js/templates/today.js"></script>
    <script src="/assets/js/templates/tools/calender.js"></script>
    <script src="/assets/js/templates/tools/playXs.js"></script>
</body>
</html>