<?php

declare(strict_types=1);

namespace alvin0319\OnixMarket\market;

use alvin0319\OnixMarket\OnixMarket;
use OnixUtils\OnixUtils;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\Position;
use function array_search;
use function array_values;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function in_array;
use function json_decode;
use function json_encode;

class MarketManager{
	use SingletonTrait;

	/** @var Market[] */
	protected array $markets = [];
	/** @var array posHash => market id */
	protected array $pos = [];

	protected array $createProcesses = [];

	public function __construct(){
		self::setInstance($this);
		$this->load();
	}

	public function load() : void{
		$plugin = OnixMarket::getInstance();

		if(file_exists($file = $plugin->getDataFolder() . "markets.json")){
			$data = json_decode(file_get_contents($file), true);
			foreach($data as $id => $marketData){
				$market = Market::jsonDeserialize($marketData);
				$this->markets[$market->getId()] = $market;
			}
		}

		if(file_exists($file = $plugin->getDataFolder() . "pos.json")){
			$this->pos = json_decode(file_get_contents($file), true);
		}
	}

	public function save() : void{
		$plugin = OnixMarket::getInstance();

		$res = [];
		foreach($this->markets as $id => $market){
			$res[$market->getId()] = $market->jsonSerialize();
		}
		file_put_contents($plugin->getDataFolder() . "markets.json", json_encode($res));

		//var_dump($this->pos);

		file_put_contents($plugin->getDataFolder() . "pos.json", json_encode($this->pos));
	}

	public function getMarket(int $id) : ?Market{
		return $this->markets[$id] ?? null;
	}

	public function getMarketByItem(Item $item) : ?Market{
		foreach($this->markets as $id => $market){
			if($market->getItem()->equals($item, true, true)){
				return $market;
			}
		}
		return null;
	}

	public function registerMarket(Item $item) : Market{
		$id = 0;
		while(isset($this->markets[$id])){
			$id++;
		}
		return $this->markets[$id] = new Market($id, $item, -1, -1);
	}

	public function unregisterMarket(Market $market) : void{
		unset($this->markets[$market->getId()]);
	}

	public function registerPosition(Position $pos, Market $market) : void{
		$this->pos[OnixUtils::posToStr($pos)] = $market->getId();
	}

	public function unregisterPosition(Position $pos) : void{
		unset($this->pos[OnixUtils::posToStr($pos)]);
	}

	public function hasMarketInPos(Position $pos) : bool{
		return isset($this->pos[OnixUtils::posToStr($pos)]);
	}

	public function getMarketInPos(Position $pos) : ?Market{
		if(!$this->hasMarketInPos($pos)){
			return null;
		}
		return $this->getMarket($this->pos[OnixUtils::posToStr($pos)]);
	}

	public function addCreateProcess(Player $player) : void{
		$this->createProcesses[] = $player->getName();
	}

	public function isInCreateProcess(Player $player) : bool{
		return in_array($player->getName(), $this->createProcesses);
	}

	public function removeCreateProcess(Player $player) : void{
		unset($this->createProcesses[array_search($player->getName(), $this->createProcesses)]);
		$this->createProcesses = array_values($this->createProcesses);
	}
}