<?php

declare(strict_types=1);

namespace alvin0319\OnixMarket\market;

use JsonSerializable;
use onebone\economyapi\EconomyAPI;
use OnixUtils\OnixUtils;
use pocketmine\item\Item;
use pocketmine\player\Player;

class Market implements JsonSerializable{
	/** @var int */
	protected int $id;
	/** @var Item */
	protected Item $item;
	/** @var int */
	protected int $buyPrice;
	/** @var int */
	protected int $sellPrice;

	public function __construct(int $id, Item $item, int $buyPrice, int $sellPrice){
		$this->id = $id;
		$this->item = $item;
		$this->buyPrice = $buyPrice;
		$this->sellPrice = $sellPrice;
	}

	public function getId() : int{
		return $this->id;
	}

	public function getItem() : Item{
		return clone $this->item;
	}

	public function setBuyPrice(int $price) : void{
		if($price < 0){
			$this->buyPrice = -1;
		}else{
			$this->buyPrice = $price;
		}
	}

	public function setSellPrice(int $price) : void{
		if($price < 0){
			$this->sellPrice = -1;
		}else{
			$this->sellPrice = $price;
		}
	}

	public function getBuyPrice() : int{
		return $this->buyPrice;
	}

	public function getSellPrice() : int{
		return $this->sellPrice;
	}

	public function buy(Player $player, int $count = 1) : void{
		$price = $this->buyPrice * $count;
		if(EconomyAPI::getInstance()->myMoney($player) < $price){
			OnixUtils::message($player, "구매에 필요한 돈이 부족합니다.");
			return;
		}
		$item = $this->getItem()->setCount($count);
		if(!$player->getInventory()->canAddItem($item)){
			OnixUtils::message($player, "인벤토리에 공간이 부족합니다.");
			return;
		}
		$before = EconomyAPI::getInstance()->myMoney($player);
		$player->getInventory()->addItem($item);
		EconomyAPI::getInstance()->reduceMoney($player, $price);
		$after = EconomyAPI::getInstance()->myMoney($player);
		$gap = $before - $after;
		OnixUtils::message($player, "§d{$item->getName()}§f {$count}개를 구매했습니다.");
		OnixUtils::message($player, "구매 전 돈: {$before}, 구매 후 돈: {$after}, 사용한 돈: {$gap}");
	}

	public function sell(Player $player, int $count) : void{
		$item = $this->getItem()->setCount($count);
		if(!$player->getInventory()->contains($item)){
			OnixUtils::message($player, "판매에 필요한 아이템이 부족합니다.");
			return;
		}
		$before = EconomyAPI::getInstance()->myMoney($player);
		$player->getInventory()->removeItem($item);
		EconomyAPI::getInstance()->addMoney($player, $this->sellPrice * $count);
		$after = EconomyAPI::getInstance()->myMoney($player);
		$gap = $after - $before;
		OnixUtils::message($player, "§d{$item->getName()}§f {$count}개를 판매했습니다.");
		OnixUtils::message($player, "판매 전 돈: {$before}, 판매 후 돈: {$after}, 수익: {$gap}");
	}

	public function jsonSerialize() : array{
		return [
			"id" => $this->id,
			"item" => $this->item->jsonSerialize(),
			"buyPrice" => $this->buyPrice,
			"sellPrice" => $this->sellPrice
		];
	}

	public static function jsonDeserialize(array $data) : Market{
		return new Market($data["id"], Item::jsonDeserialize($data["item"]), $data["buyPrice"], $data["sellPrice"]);
	}

	public function designItem() : Item{
		return $this->getItem()->setCustomName($this->item->getName() . "\n§f구매가: §d" . ($this->buyPrice >= 0 ? EconomyAPI::getInstance()->koreanWonFormat($this->buyPrice) : "§c구매 불가") . "\n§f판매가: §d" . ($this->sellPrice >= 0 ? EconomyAPI::getInstance()->koreanWonFormat($this->sellPrice) : "§c판매 불가"));
	}
}