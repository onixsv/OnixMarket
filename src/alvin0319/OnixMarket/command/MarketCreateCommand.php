<?php

declare(strict_types=1);

namespace alvin0319\OnixMarket\command;

use alvin0319\OnixMarket\market\MarketManager;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class MarketCreateCommand extends Command{

	public function __construct(){
		parent::__construct("상점생성", "상점을 생성합니다.");
		$this->setPermission("onixmarket.command.create");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testPermission($sender)){
			return false;
		}
		if(!$sender instanceof Player){
			return false;
		}
		if(MarketManager::getInstance()->isInCreateProcess($sender)){
			MarketManager::getInstance()->removeCreateProcess($sender);
			OnixUtils::message($sender, "상점 생성 작업을 중단했습니다.");
		}else{
			MarketManager::getInstance()->addCreateProcess($sender);
			OnixUtils::message($sender, "상점 생성 작업을 시작합니다. 중단하려면 \"/상점생성\" 명령어를 한번 더 입력해주세요.");
		}
		return true;
	}
}