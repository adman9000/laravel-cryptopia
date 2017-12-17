<?php namespace adman9000\cryptopia;

use Illuminate\Support\Facades\Facade;

class CryptopiaAPIFacade extends Facade {

	protected static function getFacadeAccessor() {
		return 'cryptopia';
	}
}