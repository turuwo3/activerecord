<?php

$config = require './config.php';

$pdo = new PDO('mysql:host=localhost;charset=utf8;',
	$config['Database']['MySql']['user'],
	$config['Database']['MySql']['password']);

$pdo->query("create database test character set utf8");
$pdo->query("use test");

$pdo->query("create table comments(id serial, text text, user_id int)");
$pdo->query("create table users(id serial, name text, age int)");
$pdo->query("create table profiles(id serial, user_id int, text text)");
$pdo->query("create table skills(id serial, user_id int, name text)");
$pdo->query("create table users_skills(id serial, users_id int, skills_id int)");
$pdo->query("create table players(id serial, type text, name text, club text, batting_average int, bowling_average int)");
$pdo->query("create table inheritancedependents(id serial, name text, child_id int)");
$pdo->query("create table inheritances(id serial, type text, parent text, child text)");
$pdo->query("create table mocks(id serial, name text, age int)");
$pdo->query("create table mockdependents(id serial, name text, mock_id int)");

