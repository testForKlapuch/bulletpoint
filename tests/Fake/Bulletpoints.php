<?php
namespace Bulletpoint\Fake;

use Bulletpoint\Model\{Wiki};

final class Bulletpoints implements Wiki\Bulletpoints {
	public function add(string $content, Wiki\InformationSource $source) {
		
	}

	public function iterate(): \Iterator {
		
	}
}