<?php
namespace HomeFeed;

class Section
{
	public $title;

}
class Welcome
{
	public $type = 'welcome';
	public $title = 'Welcome.';
	public $banner = 'bg/welcome-banner.jpg';
	public $message = 'TV Shows, movies and more at your fingertips. Enjoy.';
}
class Register
{
	public $type = 'register';
	public $title = 'Join community';
	public $banner = 'bg/reg-banner.jpg';

	public $media = [
		'Maintain a personal watchlist',
		'Log videos you have seen',
		'Build custom list'
	];
}


?>