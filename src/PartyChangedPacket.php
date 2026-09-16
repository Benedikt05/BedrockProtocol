<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

class PartyChangedPacket extends DataPacket implements ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::PARTY_CHANGED_PACKET;

	public ?string $partyId = null;
	public ?bool $leader = null;

	protected function decodePayload(PacketSerializer $in) : void{
		if($in->getBool()){
			$this->partyId = $in->getString();
			$this->leader = $in->getBool();
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{

	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handlePartyChanged($this);
	}
}