<?php

declare(strict_types=1);

namespace alvin0319\OnixMarket;

use alvin0319\OnixMarket\market\Market;
use alvin0319\OnixMarket\market\MarketManager;
use OnixUtils\OnixUtils;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\Server;
use function base64_decode;
use function file_exists;
use function file_get_contents;
use function json_decode;
use function rename;

class SMarketConverter{

	public function run() : void{
		$c = 0;
		/** @var Market[] $id2market */
		$id2market = [];
		if(file_exists($file = Server::getInstance()->getDataPath() . "plugin_data/SMarket/registered_markets.json")){
			$data = json_decode(file_get_contents($file), true);
			foreach($data as $id => $marketData){
				$item = ItemFactory::getInstance()->get((int) $marketData["item"]["id"], (int) $marketData["item"]["damage"], 1, (new LittleEndianNbtSerializer())->read(base64_decode($marketData["item"]["nbt_b64"]))->mustGetCompoundTag());
				$buyPrice = (int) $marketData["buyPrice"];
				$sellPrice = (int) $marketData["sellPrice"];

				$market = MarketManager::getInstance()->registerMarket($item);
				$market->setBuyPrice($buyPrice);
				$market->setSellPrice($sellPrice);
				$id2market[$id] = $market;
				$c++;
			}

			rename($file, Server::getInstance()->getDataPath() . "plugin_data/SMarket/registered_markets.json.bak");
		}
		if(file_exists($file = Server::getInstance()->getDataPath() . "plugin_data/SMarket/installed_markets.json")){
			$data = json_decode(file_get_contents($file), true);
			foreach($data as $posHash => $marketId){
				if(isset($id2market[$marketId])){
					MarketManager::getInstance()->registerPosition(OnixUtils::strToPos($posHash), $id2market[$marketId]);
				}
			}
			rename($file, Server::getInstance()->getDataPath() . "plugin_data/SMarket/installed_markets.json.bak");
		}
		if($c > 0){
			OnixMarket::getInstance()->getLogger()->notice("{$c}개의 상점 데이터를 변환했습니다.");
		}
	}
}