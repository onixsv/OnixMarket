<?php

declare(strict_types=1);

namespace alvin0319\OnixMarket\command;

use alvin0319\OnixMarket\market\MarketManager;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\player\Player;
use function array_shift;
use function count;
use function is_numeric;

class MarketSellPriceCommand extends Command{

	public function __construct(){
		parent::__construct("판매가", "상점의 구매가를 설정합니다.");
		$this->setPermission("onixmarket.command.sellprice");
		$this->setUsage("/판매가 <가격>");
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
		if(!is_numeric($count)){
			throw new InvalidCommandSyntaxException();
		}
		$count = (int) $count;

		$item = $sender->getInventory()->getItemInHand();
		$market = MarketManager::getInstance()->getMarketByItem($item);
		if($market === null){
			OnixUtils::message($sender, "해당 상점이 존재하지 않습니다.");
			return false;
		}
		$market->setSellPrice($count);
		OnixUtils::message($sender, "{$market->getItem()->getName()} 상점의 판매가를 {$count}(으)로 설정했습니다.");
		return true;
	}
}