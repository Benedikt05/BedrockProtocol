<?php

/*
 * This file is part of BedrockProtocol.
 * Copyright (C) 2014-2022 PocketMine Team <https://github.com/pmmp/BedrockProtocol>
 *
 * BedrockProtocol is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol;

use InvalidArgumentException;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use UnexpectedValueException;
use function count;

class TextPacket extends DataPacket implements ClientboundPacket, ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::TEXT_PACKET;

	public const TYPE_RAW = 0;
	public const TYPE_CHAT = 1;
	public const TYPE_TRANSLATION = 2;
	public const TYPE_POPUP = 3;
	public const TYPE_JUKEBOX_POPUP = 4;
	public const TYPE_TIP = 5;
	public const TYPE_SYSTEM = 6;
	public const TYPE_WHISPER = 7;
	public const TYPE_ANNOUNCEMENT = 8;
	public const TYPE_JSON_WHISPER = 9;
	public const TYPE_JSON = 10;
	public const TYPE_JSON_ANNOUNCEMENT = 11;

	private const ONEOF_MESSAGE_ONLY = 0;
	private const ONEOF_AUTHOR_AND_MESSAGE = 1;
	private const ONEOF_MESSAGE_AND_PARAMS = 2;

	public int $type;
	public bool $needsTranslation = false;
	public string $sourceName;
	public string $message;
	/** @var string[] */
	public array $parameters = [];
	public string $xboxUserId = "";
	public string $platformChatId = "";
	public ?string $filteredMessage = null;

	private static function messageOnly(int $type, string $message) : self{
		$result = new self;
		$result->type = $type;
		$result->message = $message;
		return $result;
	}

	/**
	 * @param string[] $parameters
	 */
	private static function baseTranslation(int $type, string $key, array $parameters) : self{
		$result = new self;
		$result->type = $type;
		$result->needsTranslation = true;
		$result->message = $key;
		$result->parameters = $parameters;
		return $result;
	}

	public static function raw(string $message) : self{
		return self::messageOnly(self::TYPE_RAW, $message);
	}

	/**
	 * @param string[]  $parameters
	 */
	public static function translation(string $key, array $parameters = []) : self{
		return self::baseTranslation(self::TYPE_TRANSLATION, $key, $parameters);
	}

	public static function popup(string $message) : self{
		return self::messageOnly(self::TYPE_POPUP, $message);
	}

	/**
	 * @param string[] $parameters
	 */
	public static function translatedPopup(string $key, array $parameters = []) : self{
		return self::baseTranslation(self::TYPE_POPUP, $key, $parameters);
	}

	/**
	 * @param string[] $parameters
	 */
	public static function jukeboxPopup(string $key, array $parameters = []) : self{
		return self::baseTranslation(self::TYPE_JUKEBOX_POPUP, $key, $parameters);
	}

	public static function tip(string $message) : self{
		return self::messageOnly(self::TYPE_TIP, $message);
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->needsTranslation = $in->getBool();
		$oneOfType = $in->getUnsignedVarInt();

		$this->type = $in->getByte();
		switch($oneOfType){
			case self::ONEOF_MESSAGE_ONLY:
				$this->message = $in->getString();
				break;

			case self::ONEOF_AUTHOR_AND_MESSAGE:
				$this->sourceName = $in->getString();
				$this->message = $in->getString();
				break;

			case self::ONEOF_MESSAGE_AND_PARAMS:
				$this->message = $in->getString();
				$count = $in->getUnsignedVarInt();
				for($i = 0; $i < $count; ++$i){
					$this->parameters[] = $in->getString();
				}
				break;
		}

		$this->xboxUserId = $in->getString();
		$this->platformChatId = $in->getString();
		$this->filteredMessage = $in->readOptional(fn() => $in->getString());
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putBool($this->needsTranslation);

		$oneOfType = $this->getOneOfType($this->type);

		$out->putByte($oneOfType);

		$out->putUnsignedVarInt($this->type);

		$message = $this->message === "" ? " " : $this->message;
		switch($oneOfType){
			case self::ONEOF_MESSAGE_ONLY:
				$out->putString($message);
				break;

			case self::ONEOF_AUTHOR_AND_MESSAGE:
				$out->putString($this->sourceName);
				$out->putString($message);
				break;

			case self::ONEOF_MESSAGE_AND_PARAMS:
				$out->putString($message);
				$out->putUnsignedVarInt(count($this->parameters));
				foreach($this->parameters as $p){
					$out->putString($p);
				}
				break;
		}

		$out->putString($this->xboxUserId);
		$out->putString($this->platformChatId);
		$out->writeOptional($this->filteredMessage, fn(string $filteredMessage) => $out->putString($filteredMessage));
	}

	protected function getOneOfType(int $textType) : int{
		return match ($textType) {
			self::TYPE_CHAT,
			self::TYPE_WHISPER,
			self::TYPE_ANNOUNCEMENT => self::ONEOF_AUTHOR_AND_MESSAGE,
			self::TYPE_TRANSLATION,
			self::TYPE_POPUP,
			self::TYPE_JUKEBOX_POPUP => self::ONEOF_MESSAGE_AND_PARAMS,
			self::TYPE_RAW,
			self::TYPE_TIP,
			self::TYPE_SYSTEM,
			self::TYPE_JSON,
			self::TYPE_JSON_WHISPER,
			self::TYPE_JSON_ANNOUNCEMENT => self::ONEOF_MESSAGE_ONLY,

			default => throw new InvalidArgumentException("Unsupported TextType " . $textType),
		};
	}


	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleText($this);
	}
}
