<?php

declare(strict_types=1);

namespace alvin0319\OnixMarket\command;

use alvin0319\OnixMarket\market\MarketManager;
use onebone\economyapi\EconomyAPI;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\player\Player;
use function array_keys;
use function array_map;
use function array_shift;
use function array_values;
use function count;
use function implode;
use function is_numeric;

class SellCommand extends Command{

	public function __construct(){
		parent::__construct("판매", "아이템을 판매합니다.");
		$this->setPermission("onixmarket.command.sell");
		$this->setUsage("/판매 <개수|전체>");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testPermission($sender)){
			return false;
		}
		if(!$sender instanceof Player){
			return false;
		}
		if(count($args) < 1){
			throw new InvalidCommandSyntaxException();
		}
		$count = array_shift($args);
		if(is_numeric($count) && ($count = (int) $count) > 0){
			$market = MarketManager::getInstance()->getMarketByItem($sender->getInventory()->getItemInHand());
			if($market === null){
				OnixUtils::message($sender, "판매할 아이템이 없습니다.");
				return false;
			}
			if($market->getSellPrice() < 0){
				OnixUtils::message($sender, "판매가 불가능한 아이템입니다.");
				return false;
			}
			$market->sell($sender, $count);
		}elseif($count === "전체"){
			$res = [];
			$price = 0;
			foreach($sender->getInventory()->getContents(false) as $item){
				$market = MarketManager::getInstance()->getMarketByItem($item);
				if($market !== null && $market->getSellPrice() >= 0){
					$sender->getInventory()->removeItem($item);
					$price += $item->getCount() * $market->getSellPrice();
					$res[$market->getItem()->getName()] = ($res[$market->getId()] ?? 0) + $item->getCount();
				}
			}
			EconomyAPI::getInstance()->addMoney($sender, $price);
			OnixUtils::message($sender, "판매 전체 결과: " . implode(", ", array_map(function(string $name, int $count) : string{
					return $name . " " . $count . "개";
				}, array_keys($res), array_values($res))));
			OnixUtils::message($sender, "얻은 돈: " . EconomyAPI::getInstance()->koreanWonFormat($price));
		}else{
			throw new InvalidCommandSyntaxException();
		}
		return true;
	}
}