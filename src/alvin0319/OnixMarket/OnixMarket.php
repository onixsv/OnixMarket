<?php

declare(strict_types=1);

namespace alvin0319\OnixMarket;

use alvin0319\OnixMarket\command\MarketBuyPriceCommand;
use alvin0319\OnixMarket\command\MarketCreateCommand;
use alvin0319\OnixMarket\command\MarketSellPriceCommand;
use alvin0319\OnixMarket\command\SellCommand;
use alvin0319\OnixMarket\market\MarketManager;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;

class OnixMarket extends PluginBase{
	use SingletonTrait;

	/** @var MarketManager */
	protected MarketManager $marketManager;

	protected function onLoad() : void{
		self::setInstance($this);
	}

	protected function onEnable() : void{
		$this->marketManager = new MarketManager();

		$this->getScheduler()->scheduleDelayedTask(new ClosureTask(function() : void{
			(new SMarketConverter())->run();
		}), 20);

		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

		$this->getServer()->getCommandMap()->registerAll("onixmarket", [
			new MarketBuyPriceCommand(),
			new MarketCreateCommand(),
			new MarketSellPriceCommand(),
			new SellCommand()
		]);
	}

	protected function onDisable() : void{
		$this->marketManager->save();
	}
}