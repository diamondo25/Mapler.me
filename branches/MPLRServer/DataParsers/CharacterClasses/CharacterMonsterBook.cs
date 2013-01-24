﻿using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    class CharacterMonsterBook
    {
        public int ChosenCardID { get; private set; }

        public void Decode(MaplePacket pPacket)
        {
            ChosenCardID = pPacket.ReadInt();

            // Unknown stuff...
            if (pPacket.ReadBool() == false)
            {
                for (short cards = pPacket.ReadShort(); cards > 0; cards--)
                {
                    pPacket.ReadShort(); // CardID
                    pPacket.ReadByte(); // Level
                }
            }
            else
            {
                pPacket.ReadShort();
                short size = pPacket.ReadShort();
                pPacket.Skip(size); // Card block

                size = pPacket.ReadShort();
                pPacket.Skip(size); // Levels
            }
        }
    }
}
