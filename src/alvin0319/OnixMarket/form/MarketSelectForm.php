<?php

declare(strict_types=1);

namespace alvin0319\OnixMarket\form;

use alvin0319\OnixMarket\market\Market;
use onebone\economyapi\EconomyAPI;
use OnixUtils\OnixUtils;
use pocketmine\form\Form;
use pocketmine\player\Player;
use function count;
use function is_array;
use function is_numeric;

class MarketSelectForm implements Form{
	/** @var Market */
	protected Market $market;

	public function __construct(Market $market){
		$this->market = $market;
	}

	public function jsonSerialize() : array{
		$marketText = "§f구매가: §b" . ($this->market->getBuyPrice() >= 0 ? EconomyAPI::getInstance()->koreanWonFormat($this->market->getBuyPrice()) : "§c구매 불가") . "§f\n판매가: §b" . ($this->market->getSellPrice() >= 0 ? EconomyAPI::getInstance()->koreanWonFormat($this->market->getSellPrice()) : "§c판매 불가");
		return [
			"type" => "custom_form",
			"title" => "{$this->market->getItem()->getName()} 구매",
			"content" => [
				[
					"type" => "label",
					"text" => $marketText
				],
				[
					"type" => "dropdown",
					"text" => "구매 / 판매",
					"options" => ["구매", "판매"]
				],
				[
					"type" => "input",
					"text" => "수량을 입력해주세요."
				]
			]
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_array($data) || count($data) !== 3){
			return;
		}
		[, $buyOrSell, $count] = $data;
		if(!is_numeric($count) || ($count = (int) $count) < 1){
			OnixUtils::message($player, "수량을 정확히 입력해주세요.");
			return;
		}
		$method = $buyOrSell === 0 ? "buy" : "sell";

		if($buyOrSell === 0){
			if($this->market->getBuyPrice() < 0){
				OnixUtils::message($player, "구매가 불가능한 아이템입니다.");
				return;
			}
		}elseif($buyOrSell === 1){
			if($this->market->getSellPrice() < 0){
				OnixUtils::message($player, "판매가 불가능한 아이템입니다.");
				return;
			}
		}

		$this->market->{$method}($player, $count);
	}
}