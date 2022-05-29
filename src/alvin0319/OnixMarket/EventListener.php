<?php

declare(strict_types=1);

namespace alvin0319\OnixMarket;

use alvin0319\OnixMarket\form\MarketSelectForm;
use alvin0319\OnixMarket\market\MarketManager;
use OnixUtils\OnixUtils;
use pocketmine\block\ItemFrame;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ItemFrameDropItemPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\types\PlayerBlockActionWithBlockInfo;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

class EventListener implements Listener{

	public function onDataPacketReceive(DataPacketReceiveEvent $event) : void{
		$packet = $event->getPacket();
		$player = $event->getOrigin()->getPlayer();
		if($packet instanceof ItemFrameDropItemPacket){
			$pos = new Position($packet->x, $packet->y, $packet->z, $player->getWorld());
			$block = $player->getWorld()->getBlock($pos);
			if($block instanceof ItemFrame){
				if(MarketManager::getInstance()->hasMarketInPos($pos)){
					$event->cancel();
					if(!$player->hasPermission("onixmarket.market.delete")){
						$player->sendPopup(TextFormat::RED . "상점을 부술 수 없습니다.");
						return;
					}
					MarketManager::getInstance()->unregisterPosition($pos);
					OnixUtils::message($player, "상점을 제거했습니다.");
					$block->setFramedItem(null);
					$block->getPosition()->getWorld()->setBlock($pos, $block);
				}
			}
		}elseif($packet instanceof PlayerAuthInputPacket){
			if($packet->getBlockActions() !== null){
				foreach($packet->getBlockActions() as $action){
					if($action instanceof PlayerBlockActionWithBlockInfo){
						$pos = new Position($action->getBlockPosition()->getX(), $action->getBlockPosition()->getY(), $action->getBlockPosition()->getZ(), $player->getWorld());
						$block = $player->getWorld()->getBlock($pos);
						if($block instanceof ItemFrame){
							if(MarketManager::getInstance()->hasMarketInPos($pos)){
								$market = MarketManager::getInstance()->getMarketInPos($pos);
								$player->sendForm(new MarketSelectForm($market));
								$event->cancel();

								$block->setFramedItem($market->designItem());
								$block->getPosition()->getWorld()->setBlock($block->getPosition(), $block);
							}elseif(MarketManager::getInstance()->isInCreateProcess($player)){
								$item = $player->getInventory()->getItemInHand();
								if(!$item->isNull()){
									$market = MarketManager::getInstance()->getMarketByItem($item);
									if($market === null){
										$market = MarketManager::getInstance()->registerMarket($item);
									}
									MarketManager::getInstance()->registerPosition($pos, $market);
									OnixUtils::message($player, "상점을 생성했습니다.");
									$block->setFramedItem($market->designItem());
									$block->getPosition()->getWorld()->setBlock($pos, $block);
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * @param PlayerInteractEvent $event
	 *
	 * @handleCancelled true
	 */
	public function onPlayerInteract(PlayerInteractEvent $event) : void{
		$block = $event->getBlock();
		if($block instanceof ItemFrame){
			if(MarketManager::getInstance()->hasMarketInPos($block->getPosition())){
				$event->cancel();
			}
		}
	}
}