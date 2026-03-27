<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

class PartyChangedPacket extends DataPacket implements ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::PARTY_CHANGED_PACKET;

	public ?string $partyId = null;

	protected function decodePayload(PacketSerializer $in) : void{
		$this->partyId = $in->readOptional(fn() => $in->getString());
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->writeOptional($this->partyId, fn(string $partyId) => $out->putString($partyId));
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handlePartyChanged($this);
	}
}