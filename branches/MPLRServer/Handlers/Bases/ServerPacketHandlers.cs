﻿using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    public abstract class IServerPacketHandlers
    {
        public virtual void HandleLogin(ClientConnection pConnection, MaplePacket pPacket)
        {
            int error = pPacket.ReadInt();
            pPacket.ReadShort();
            if (error != 0)
            {
                pConnection.Logger_WriteLine("Got Status: {0}", error);
                if (error == 0x07)
                {
                    pConnection.Logger_WriteLine("Already logged in!");
                }
                return;
            }

            int userid = pPacket.ReadInt();
            byte gender = pPacket.ReadByte(); // Gender or GenderSelect/PinSelect
            pPacket.ReadByte();
            short admin = pPacket.ReadShort();
            pPacket.ReadInt(); // ReadBytes(4)
            pPacket.ReadByte(); // 0x95
            string username = pPacket.ReadString(); // Username

            pPacket.ReadByte(); // 0?


            byte qban = pPacket.ReadByte(); // Quiet Ban
            DateTime qban_time = DateTime.FromFileTime(pPacket.ReadLong()); // Quiet Ban Time
            DateTime creationtime = DateTime.FromFileTime(pPacket.ReadLong()); // Creation Time
            pPacket.ReadInt(); // 78?
            pPacket.Skip(2); // 1 1
            pPacket.ReadBytes(8); // CC key

            ParseLogin(pConnection, userid, username, creationtime);
        }

        public virtual void HandleLoginFromWeb(ClientConnection pConnection, MaplePacket pPacket)
        {
            byte error = pPacket.ReadByte();
            if (error != 0)
            {
                pConnection.Logger_WriteLine("Got Status: {0}", error);
                if (error == 0x07)
                {
                    pConnection.Logger_WriteLine("Already logged in!");
                }
                return;
            }

            int userid = pPacket.ReadInt();
            pPacket.ReadByte(); // Gender or GenderSelect/PinSelect
            pPacket.ReadByte();
            pPacket.ReadShort(); // Admin info!
            pPacket.ReadInt(); // ReadBytes(4)
            pPacket.ReadByte(); // 0x95
            string username = pPacket.ReadString(); // Username

            pPacket.ReadByte(); // 0?


            pPacket.ReadByte(); // Quiet Ban
            pPacket.ReadLong(); // Quiet Ban Time
            pPacket.ReadString(); // Username. Again.
            DateTime creationtime = DateTime.FromFileTime(pPacket.ReadLong()); // creation datetime
            pPacket.ReadInt();
            pPacket.ReadBytes(8); // CC key
            pPacket.ReadString();

            ParseLogin(pConnection, userid, username, creationtime);
        }

        protected void ParseLogin(ClientConnection pConnection, int pUserID, string pUsername, DateTime pCreationDate)
        {
            pConnection.Logger_WriteLine("User logged into Nexon account '{1}', userid {0}", pUserID, pUsername);

            if (AccountDataCache.Instance.KnownUserlist.ContainsKey(pUserID))
            {
                int tmp = AccountDataCache.Instance.KnownUserlist[pUserID];
                if (tmp == 2)
                {
                    pConnection.Logger_WriteLine("User bound to temporary account. Allocating current account to it.");

                    using (UpdateQueryBuilder q = new UpdateQueryBuilder("users"))
                    {
                        q.SetColumn("account_id", pConnection.AccountID);
                        q.SetColumn("last_check", MySQL_Connection.NOW);
                        q.SetColumn("creation_date", pCreationDate);
                        q.SetWhereColumn("ID", pUserID);
                        q.RunQuery();
                    }

                    AccountDataCache.Instance.KnownUserlist[pUserID] = pConnection.AccountID;
                    pConnection.UserID = pUserID;
                }
                else if (tmp == pConnection.AccountID)
                {
                    // Correct account, continue
                    pConnection.Logger_WriteLine("User bound to same account. kay");

                    using (UpdateQueryBuilder q = new UpdateQueryBuilder("users"))
                    {
                        q.SetColumn("last_check", MySQL_Connection.NOW);
                        q.SetWhereColumn("ID", pUserID);
                        q.RunQuery();
                    }

                    pConnection.UserID = pUserID;
                }
                else
                {
                    pConnection.Logger_WriteLine("User ID not bound to this account!!! Ignoring...");
                    pConnection.SendInfoText("WARNING: This Nexon account ({0}) is not yours! Please login into the correct Mapler.me account.", pUsername);
                    return;
                }
            }
            else
            {
                pConnection.Logger_WriteLine("Creating user for accountID {0}", pConnection.AccountID);

                using (InsertQueryBuilder insertq = new InsertQueryBuilder("users"))
                {
                    insertq.OnDuplicateUpdate = true;

                    insertq.AddColumn("account_id");
                    insertq.AddColumn("ID");
                    insertq.AddColumn("creation_date");
                    insertq.AddColumn("last_check", true);
                    insertq.AddColumn("maplepoints");

                    insertq.AddRow(pConnection.AccountID, pUserID, pCreationDate, MySQL_Connection.NOW, 0);
                    insertq.RunQuery();
                }

                pConnection.UserID = pUserID;
                AccountDataCache.Instance.KnownUserlist.Add(pUserID, pConnection.AccountID);
            }


            pConnection.SendInfoText("Identified account {0}. You can now select your character.", pUsername);

            if (pConnection.LogFilename == "Unknown")
                pConnection.LogFilename = "";
            else
                pConnection.LogFilename += "_";
            pConnection.LogFilename += pConnection.AccountID.ToString();

            // Save IP of loginserver
            if (pConnection.ConnectedToIP != null)
                Queries.SaveServerIP(pConnection.ConnectedToIP, pConnection.ConnectedToPort, 0, 0);
        }


        private static void ParseAvatar(MaplePacket pPacket)
        {
            pPacket.ReadByte(); // Gender
            pPacket.ReadByte(); // Skin
            pPacket.ReadInt(); // Face
            int jobid = pPacket.ReadInt(); // Job

            pPacket.ReadByte(); // Hair slot (0)
            pPacket.ReadInt(); // Hair

            for (int i = 1; i != 0xFF; i++) // Hidden
            {
                byte slot = pPacket.ReadByte(); // Slot
                if (slot == 0xFF) break;
                pPacket.ReadInt(); // Item ID
            }

            for (int i = 1; i != 0xFF; i++) // Shown
            {
                byte slot = pPacket.ReadByte(); // Slot
                if (slot == 0xFF) break;
                pPacket.ReadInt(); // Item ID
            }

            for (int i = 1; i != 0xFF; i++) // Dunno
            {
                byte slot = pPacket.ReadByte(); // Slot
                if (slot == 0xFF) break;
                pPacket.ReadInt(); // Item ID
            }

            pPacket.ReadInt(); // Cash equip
            pPacket.ReadInt(); // Cash equip
            pPacket.ReadInt(); // Cash equip

            pPacket.ReadByte(); // 0?

            pPacket.ReadInt(); // Pet ID 1
            pPacket.ReadInt(); // Pet ID 2
            pPacket.ReadInt(); // Pet ID 3

            if (jobid / 100 == 31 || jobid / 100 == 36 || jobid == 3001 || jobid == 3002)
            {
                pPacket.ReadInt(); // Scar?
            }
        }


        public virtual void HandleCharacterDeletion(ClientConnection pConnection, MaplePacket pPacket)
        {
            int id = pPacket.ReadInt();
            bool notok = pPacket.ReadBool();

            if (!notok && pConnection.UserID != -1)
            {
                pConnection.Logger_WriteLine("Deleting character with ID {0}", id);

                // Oh jolly...
                // *crosses fingers*

                // Delete from local cache
                int internalid = 0;
                if (AccountDataCache.Instance.DeleteCharacterInfo(id, pConnection.WorldID, pConnection.AccountID, out internalid))
                {

                    MySQL_Connection.Instance.RunQuery("DELETE FROM characters WHERE internal_id = " + internalid);
                }
                else
                {
                    Logger.WriteLine("Failed to delete character. Invalid accountid, worldid, characterid or maybe not stored?");
                }
            }
            else
            {
                pConnection.Logger_WriteLine("Account failed deleting ID {0}", id);
            }
        }

        public virtual void HandleTradeData(ClientConnection pConnection, MaplePacket pPacket)
        {
            byte type = pPacket.ReadByte();
            pPacket.ReadByte();
            if (type == 0x0A)
            {
                pPacket.ReadByte();
                pPacket.ReadShort();
                int merchid = pPacket.ReadInt();
                string merchname = pPacket.ReadString();
                pPacket.ReadByte();
                while (true)
                {
                    byte slot = pPacket.ReadByte();
                    if (slot == 255) break;

                    pPacket.ReadInt(); // Player ID
                    ParseAvatar(pPacket);
                    pPacket.ReadString(); // Player Name
                    pPacket.ReadShort(); // Job
                }

                pPacket.ReadShort();
                pPacket.ReadString(); // Owner name
                pPacket.ReadShort(); // Shop cash ID
                pPacket.ReadString(); // Shop name
                pPacket.ReadInt(); // ???
                pPacket.ReadByte(); // ???

                byte items = pPacket.ReadByte();

                using (InsertQueryBuilder itemsTable = new InsertQueryBuilder("items_trades"))
                {
                    for (int i = 0; i < items; i++)
                    {
                        short slot = pPacket.ReadShort(); // Slot?
                        pPacket.ReadShort();
                        pPacket.ReadInt(); // Price
                        Queries.SaveItem(pConnection, (ushort)0, slot, ItemBase.DecodeItemData(pConnection, pPacket), itemsTable, true);
                    }

                    MySQL_Connection.Instance.RunQuery("DELETE FROM items_trades"); // Clear table

                    itemsTable.RunQuery();
                }
            }
        }

        public virtual void HandleSpawnPlayer(ClientConnection pConnection, MaplePacket pPacket)
        {
            int id = pPacket.ReadInt();
            byte level = pPacket.ReadByte();
#if LOCALE_EMS
            pPacket.ReadByte();
#endif
            string name = pPacket.ReadString();
            string successor = pPacket.ReadString();
            string guildname = pPacket.ReadString();
            // pConnection.Logger_WriteLine("I see {0}! ID {1} Level {2}{3}, guild {4}", name, id, level, successor.Length == 0 ? "" : " (" + name + "'s Successor)", guildname);

            Queries.SeePlayer(id, name, GameHelper.GetAllianceWorldID(pConnection.WorldID), level, guildname, pConnection.CharData.Stats.MapID, pConnection.CharacterInternalID);

            if (!pConnection._CharactersInMap.Contains(name))
                pConnection._CharactersInMap.Add(name);
        }

        public virtual void HandleMaplePointAmount(ClientConnection pConnection, MaplePacket pPacket)
        {
            int amount = pPacket.ReadInt(); // Life can be so easy

            using (UpdateQueryBuilder q = new UpdateQueryBuilder("users"))
            {
                q.SetWhereColumn("account_id", pConnection.AccountID);
                q.SetColumn("maplepoints", amount);
                q.RunQuery();
            }
        }


        public virtual void HandleMessage(ClientConnection pConnection, MaplePacket pPacket)
        {
            byte type = pPacket.ReadByte();
            if (type == 0x0C)
            {
                ushort id = pPacket.ReadUShort();
                string data = pPacket.ReadString();
                // Server data update
                using (InsertQueryBuilder iqb = new InsertQueryBuilder("quests_running_party"))
                {
                    iqb.OnDuplicateUpdate = true;
                    iqb.AddColumn("character_id");
                    iqb.AddColumn("questid");
                    iqb.AddColumn("data", true);

                    iqb.AddRow(pConnection.CharacterInternalID, id, data);
                    iqb.RunQuery();
                }
            }
            else if (type == 0x01)
            {
                ushort id = pPacket.ReadUShort();
                byte mode = pPacket.ReadByte();

                if (mode == 1)
                {
                    string text = pPacket.ReadString();

                    using (InsertQueryBuilder iqb = new InsertQueryBuilder("quests_running"))
                    {
                        iqb.OnDuplicateUpdate = true;
                        iqb.AddColumn("character_id");
                        iqb.AddColumn("questid");
                        iqb.AddColumn("data", true);

                        iqb.AddRow(pConnection.CharacterInternalID, id, text);
                        iqb.RunQuery();
                    }

                }
                else if (mode == 2)
                {
                    // Quest complete
                    long time = pPacket.ReadLong();

                    using (DeleteQueryBuilder dqb = new DeleteQueryBuilder("quests_running"))
                    {
                        dqb.SetWhereColumn("character_id", pConnection.CharacterInternalID);
                        dqb.SetWhereColumn("questid", id);
                        dqb.RunQuery();
                    }

                    using (InsertQueryBuilder iqb = new InsertQueryBuilder("quests_done"))
                    {
                        iqb.OnDuplicateUpdate = true;
                        iqb.AddColumn("character_id");
                        iqb.AddColumn("questid");
                        iqb.AddColumn("time", true);

                        iqb.AddRow(pConnection.CharacterInternalID, id, time);
                        iqb.RunQuery();
                    }

                }
            }
        }

        public virtual void HandleSpawnAndroid(ClientConnection pConnection, MaplePacket pPacket)
        {
            Android android = new Android();
            android.Decode(pPacket);

            if (android.ID == pConnection.CharacterID)
            {
                // Save android
                using (InsertQueryBuilder iqb = new InsertQueryBuilder("androids"))
                {
                    iqb.OnDuplicateUpdate = true;
                    iqb.AddColumn("character_id");
                    iqb.AddColumns(true, "name", "type", "skin", "hair", "face");
                    for (int i = 1; i <= 7; i++)
                        iqb.AddColumn("equip" + i, true);

                    iqb.AddRow(pConnection.CharacterInternalID, android.Name, android.Type, android.Skin, android.Hair, android.Face,
                        android.Equips[0], android.Equips[1], android.Equips[2],
                        android.Equips[3], android.Equips[4], android.Equips[5],
                        android.Equips[6]);
                    iqb.RunQuery();
                }
                Logger.WriteLine("Saved android '{0}' of {1}.", android.Name, pConnection.CharData.Stats.Name);

            }
        }

        public virtual void HandleGuild(ClientConnection pConnection, MaplePacket pPacket)
        {
            byte type = pPacket.ReadByte();
            if (type == 0x20)
            {
                bool hasGuild = pPacket.ReadBool();
                if (hasGuild)
                {
                    Guild guild = new Guild();
                    guild.Decode(pPacket);
                    guild.Save(pConnection.WorldID);

                    pConnection.Logger_WriteLine("{0} must be in Guild {1}", pConnection.LastLoggedCharacterName, guild.Name);
                }
                else
                {
                    // Not in a guild, anymore?
                    MySQL_Connection.Instance.RunQuery("DELETE FROM guild_members WHERE character_id = " + pConnection.CharacterInternalID + " AND world_id = " + GameHelper.GetAllianceWorldID(pConnection.WorldID));
                }
            }
        }

        public virtual void HandleAlliance(ClientConnection pConnection, MaplePacket pPacket)
        {
            byte type = pPacket.ReadByte();
            if (type == 0x0C)
            {
                bool hasAlliance = pPacket.ReadBool();
                if (hasAlliance)
                {
                    Alliance alliance = new Alliance();
                    alliance.Decode(pPacket);
                    alliance.Save(pConnection.WorldID);
                    pConnection.Logger_WriteLine("{0} must be in Alliance {1}", pConnection.LastLoggedCharacterName, alliance.Name);
                }
            }
            else if (type == 0x0D)
            {
                Alliance.DecodeGuilds(pPacket, pConnection.WorldID);
            }
        }

        public virtual bool CheckFlag(long pFlag, long pExpectedFlag)
        {
            return (pFlag & pExpectedFlag) == pExpectedFlag;
        }


        public virtual void HandleStatUpdate(ClientConnection pConnection, MaplePacket pPacket)
        {
            pPacket.ReadByte();
            long updateFlag = pPacket.ReadLong();
            if (updateFlag == 0) return; // Fake Update -.- / Unstuck

            bool didsomething = false;

            if (CheckFlag(updateFlag, 1)) // Skin
            {
                didsomething = true;
                pConnection.CharData.Stats.Skin = pPacket.ReadByte();
            }
            if (CheckFlag(updateFlag, 2)) // Eyes
            {
                didsomething = true;
                pConnection.CharData.Stats.Face = pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 4)) // Eyes
            {
                didsomething = true;
                pConnection.CharData.Stats.Hair = pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 8))
            {
                didsomething = true;
                pConnection.CharData.Stats.Pets[0] = pPacket.ReadLong();
            }
            if (CheckFlag(updateFlag, 0x80000))
            {
                var value = pPacket.ReadLong();
                pConnection.Logger_WriteLine("0x80000 | {0}", value);
            }
            if (CheckFlag(updateFlag, 0x100000))
            {
                var value = pPacket.ReadLong();
                pConnection.Logger_WriteLine("0x100000 | {0}", value);
            }
            if (CheckFlag(updateFlag, 0x10))
            {
                didsomething = true;
                var level = pPacket.ReadByte();
                Timeline.Instance.PushLevelUP(pConnection, level);
                pConnection.CharData.Stats.Level = level;
                pConnection.Logger_WriteLine("{0} leveled up to level {1}!!!", pConnection.CharData.Stats.Name, level);
            }
            if (CheckFlag(updateFlag, 0x20))
            {
                didsomething = true;
                var jobid = pPacket.ReadShort();
                Timeline.Instance.PushJobUP(pConnection, (ushort)jobid);
                pConnection.CharData.Stats.JobID = jobid;
                pConnection.Logger_WriteLine("{0} changed to job {1}!!!", pConnection.CharData.Stats.Name, jobid);
            }
            if (CheckFlag(updateFlag, 0x40))
            {
                didsomething = true;
                pConnection.CharData.Stats.Str = pPacket.ReadShort();
            }
            if (CheckFlag(updateFlag, 0x80))
            {
                didsomething = true;
                pConnection.CharData.Stats.Dex = pPacket.ReadShort();
            }
            if (CheckFlag(updateFlag, 0x100))
            {
                didsomething = true;
                pConnection.CharData.Stats.Int = pPacket.ReadShort();
            }
            if (CheckFlag(updateFlag, 0x200))
            {
                didsomething = true;
                pConnection.CharData.Stats.Luk = pPacket.ReadShort();
            }
            if (CheckFlag(updateFlag, 0x400))
            {
                didsomething = true;
                pConnection.CharData.Stats.HP = pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 0x800))
            {
                didsomething = true;
                pConnection.CharData.Stats.MaxHP = pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 0x1000))
            {
                didsomething = true;
                pConnection.CharData.Stats.MP = pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 0x2000))
            {
                didsomething = true;
                pConnection.CharData.Stats.MaxMP = pPacket.ReadInt();
            }
            if (CheckFlag(updateFlag, 0x4000))
            {
                didsomething = true;
                pConnection.CharData.Stats.AP = pPacket.ReadShort();
            }
            if (CheckFlag(updateFlag, 0x8000))
            {
                didsomething = true;

                short a1 = pConnection.CharData.Stats.JobID;
                pConnection.CharData.Stats.SPData.Clear();

                if (GameHelper.IsExtendedSPJob(pConnection.CharData.Stats.JobID))
                {

                    byte amnt = pPacket.ReadByte();
                    List<byte> haslist = new List<byte>();
                    for (int j = 0; j < amnt; j++)
                    {
                        byte v1 = pPacket.ReadByte(); // Job ID
                        int v2 = pPacket.ReadInt(); // Amount
                        pConnection.CharData.Stats.SPData.Add(new KeyValuePair<byte, int>(v1, v2));

                        haslist.Add(v1);
                    }
                    for (byte j = 1; j < 20; j++)
                    {
                        if (!haslist.Contains(j))
                            pConnection.CharData.Stats.SPData.Add(new KeyValuePair<byte, int>(j, 0));
                    }

                }
                else
                {
                    pConnection.CharData.Stats.SPData.Add(new KeyValuePair<byte, int>(0, pPacket.ReadShort()));
                }
            }

            if (CheckFlag(updateFlag, 0x10000))
            {
                didsomething = true;
                long newexp = pPacket.ReadLong();
                byte point = (byte)EXPTable.GetLevelPercentage(pConnection.CharData.Stats.Level, newexp);

                if (pConnection.LastExpPoint != point)
                {
                    // Ohhh
                    Timeline.Instance.PushExpPoint(pConnection, point);
                }

                pConnection.CharData.Stats.EXP = newexp;
                pConnection.LastExpPoint = point;

            }

            if (CheckFlag(updateFlag, 0x20000))
            {
                didsomething = true;
                int fame = pPacket.ReadInt();
                Timeline.Instance.PushGotFame(pConnection, fame - pConnection.CharData.Stats.Fame, fame);
                pConnection.CharData.Stats.Fame = fame;
            }

            if (CheckFlag(updateFlag, 0x40000))
            {
                didsomething = true;
                pConnection.CharData.Stats.Mesos = pPacket.ReadLong();
            }
            if (CheckFlag(updateFlag, 0x200000))
            {
                var value = pPacket.ReadInt();
                pConnection.Logger_WriteLine("0x200000 | {0}", value);
            }

            if (CheckFlag(updateFlag, 0x400000))
            {
                var value = pPacket.ReadByte();
                pConnection.Logger_WriteLine("0x400000 | {0}", value);
            }

            if (CheckFlag(updateFlag, 0x800000))
            {
                // Ambition/Charisma D:
                pConnection.CharData.Stats.Traits[(int)GW_CharacterStat.TraitVals.Charisma] = pPacket.ReadInt();
            }

            if (CheckFlag(updateFlag, 0x1000000))
            {
                pConnection.CharData.Stats.Traits[(int)GW_CharacterStat.TraitVals.Insight] = pPacket.ReadInt();
                didsomething = true;
            }

            if (CheckFlag(updateFlag, 0x2000000))
            {
                pConnection.CharData.Stats.Traits[(int)GW_CharacterStat.TraitVals.Willpower] = pPacket.ReadInt();
                didsomething = true;
            }

            if (CheckFlag(updateFlag, 0x4000000))
            {
                pConnection.CharData.Stats.Traits[(int)GW_CharacterStat.TraitVals.CraftDiligence] = pPacket.ReadInt();
                didsomething = true;
            }

            if (CheckFlag(updateFlag, 0x8000000))
            {
                pConnection.CharData.Stats.Traits[(int)GW_CharacterStat.TraitVals.Empathy] = pPacket.ReadInt();
                didsomething = true;
            }

            if (CheckFlag(updateFlag, 0x10000000))
            {
                pConnection.CharData.Stats.Traits[(int)GW_CharacterStat.TraitVals.Charm] = pPacket.ReadInt();
                didsomething = true;
            }

            if (CheckFlag(updateFlag, 0x20000000))
            {
                pPacket.ReadBytes(21);
            }

            if (CheckFlag(updateFlag, 0x40000000))
            {
                pPacket.ReadByte();
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadInt();
                pPacket.ReadByte();
            }

            if (CheckFlag(updateFlag, 0x80000000))
            {
                for (byte i = 0; i < 9; i++)
                {
                    pPacket.ReadInt();
                    pPacket.ReadByte();
                    pPacket.ReadInt();
                }
            }

            if (CheckFlag(updateFlag, 0x100000000))
            {
                var value1 = pPacket.ReadByte();
                var value2 = pPacket.ReadByte();
                pConnection.Logger_WriteLine("0x100000000 | {0} | {1}", value1, value2);
            }

            if (CheckFlag(updateFlag, 0x200000000))
            {
                var value = pPacket.ReadInt();
                pConnection.Logger_WriteLine("0x200000000 | {0}", value);
            }

            if (didsomething)
            {
                pConnection.CharData.SaveCharacterInfo(pConnection);

                pConnection.SendTimeUpdate();
            }
        }


        public virtual void HandleSkillMacros(ClientConnection pConnection, MaplePacket pPacket)
        {
            byte count = pPacket.ReadByte();
            if (count == 0) return;
            string q = string.Format("DELETE FROM skillmacros WHERE character_id = {0};\r\n", pConnection.CharacterInternalID);
            q += "INSERT INTO skillmacros VALUES \r\n";
            for (int i = 0; i < count; i++)
            {
                string name = pPacket.ReadString();
                bool shout = pPacket.ReadBool();
                int skill1 = pPacket.ReadInt();
                int skill2 = pPacket.ReadInt();
                int skill3 = pPacket.ReadInt();
                q += string.Format("({0}, {1}, '{2}', {3}, {4}, {5}, {6}),", pConnection.CharacterInternalID, i, MySql.Data.MySqlClient.MySqlHelper.EscapeString(name), shout, skill1, skill2, skill3);
            }
            q = q.TrimEnd(',');
            MySQL_Connection.Instance.RunQuery(q);
        }

        public virtual void HandleSkillUpdate(ClientConnection pConnection, MaplePacket pPacket)
        {
            pPacket.ReadByte(); // Unstuck
            pPacket.ReadByte();
            ushort amount = pPacket.ReadUShort();

            using (InsertQueryBuilder skillTable = new InsertQueryBuilder("skills"))
            {
                skillTable.OnDuplicateUpdate = true;
                skillTable.AddColumn("character_id", false);
                skillTable.AddColumn("skillid", false);
                skillTable.AddColumn("level", true);
                skillTable.AddColumn("maxlevel", true);
                skillTable.AddColumn("expires", true);

                for (ushort i = 0; i < amount; i++)
                {
                    int skillid = pPacket.ReadInt();
                    int level = pPacket.ReadInt();
                    int masterlevel = pPacket.ReadInt();
                    long expiration = pPacket.ReadLong();

                    Timeline.Instance.PushSkillUP(pConnection, skillid, level);

                    skillTable.AddRow(pConnection.CharacterInternalID, skillid, level, masterlevel == 0 ? null : (object)masterlevel, expiration);
                }

                if (skillTable.RowCount > 0)
                {
                    string q = skillTable.ToString();
                    System.IO.File.WriteAllText("insert-update-skills-packet.sql", q);
                    int result = (int)MySQL_Connection.Instance.RunQuery(q);
                    pConnection.Logger_WriteLine("Result Skills: {0}", result);

                    pConnection.SendTimeUpdate();
                }

            }
        }

        public virtual void HandleFamiliarList(ClientConnection pConnection, MaplePacket pPacket)
        {
            using (InsertQueryBuilder familiars = new InsertQueryBuilder("familiars"))
            {
                familiars.OnDuplicateUpdate = true;

                familiars.AddColumn("character_id");
                familiars.AddColumn("mobid");
                familiars.AddColumns(true,
                    "name", "fitality_cur", "fitality_max",
                    "starttime", "endtime", "unktime"
                );

                for (int i = pPacket.ReadInt(); i > 0; i--)
                {
                    pPacket.ReadInt(); // Weird id?
                    int mobid = pPacket.ReadInt();
                    string name = pPacket.ReadString(13);
                    byte currentfit = pPacket.ReadByte();
                    byte maxfit = pPacket.ReadByte();
                    pPacket.ReadInt();
                    pPacket.ReadByte();
                    DateTime starttime = DateTime.FromFileTime(pPacket.ReadLong());
                    DateTime unktime = DateTime.FromFileTime(pPacket.ReadLong());
                    DateTime endtime = DateTime.FromFileTime(pPacket.ReadLong());

                    pPacket.ReadByte();
                    pPacket.ReadByte();


                    familiars.AddRow(
                        pConnection.CharacterInternalID,
                        mobid,
                        name,
                        currentfit,
                        maxfit,
                        starttime,
                        endtime,
                        unktime
                        );

                }

                familiars.RunQuery();
            }
        }

        public virtual void HandleBuddyList(ClientConnection pConnection, MaplePacket pPacket)
        {
            byte mode = pPacket.ReadByte();
            if (mode != 0x07) return;

            MySQL_Connection.Instance.RunQuery(string.Format("DELETE FROM buddies WHERE character_id = {0}", pConnection.CharacterInternalID));
            using (InsertQueryBuilder buddies = new InsertQueryBuilder("buddies"))
            {
                buddies.OnDuplicateUpdate = true;
                buddies.AddColumn("character_id");
                buddies.AddColumn("friend_id");
                buddies.AddColumns(true, "friend_name", "group_name");

                byte amount = pPacket.ReadByte();
                for (byte i = 0; i < amount; i++)
                {
                    int bid = pPacket.ReadInt();
                    string bname = pPacket.ReadString(13);
                    pPacket.Skip(1 + 4);
                    string gname = pPacket.ReadString(13);
                    pPacket.Skip(4);

                    buddies.AddRow(
                        pConnection.CharacterInternalID,
                        bid,
                        bname,
                        gname
                        );
                }
                buddies.RunQuery();
            }
        }

        public virtual void HandleAbilityInfoUpdate(ClientConnection pConnection, MaplePacket pPacket)
        {
            pPacket.ReadByte(); // Unlock
            if (pPacket.ReadBool() == false) return;

            var stat = new Tuple<byte, int, byte>((byte)pPacket.ReadShort(), pPacket.ReadInt(), (byte)pPacket.ReadShort());
            pPacket.ReadShort();

            using (InsertQueryBuilder table = new InsertQueryBuilder("character_abilities"))
            {
                table.OnDuplicateUpdate = true;

                table.AddColumn("character_id");
                table.AddColumn("id");
                table.AddColumn("skill_id", true);
                table.AddColumn("level", true);


                table.AddRow(
                    pConnection.CharacterInternalID,
                    stat.Item1,
                    stat.Item2,
                    stat.Item3
                    );

                table.RunQuery();
            }
        }

        public virtual void HandleInventorySlotsUpdate(ClientConnection pConnection, MaplePacket pPacket)
        {
            CharacterInventory inventory = pConnection.CharData.Inventory;
            byte inv = pPacket.ReadByte();
            byte newslots = pPacket.ReadByte();
            if (inv < 1 || inv > 5) return;
            if (newslots < 24 || newslots > 96) return; // Just to be sure
            inventory.InventorySlots[inv - 1] = newslots;

            string slotname = "";

            switch (inv)
            {
                case 1: slotname = "eqp"; break;
                case 2: slotname = "use"; break;
                case 3: slotname = "setup"; break;
                case 4: slotname = "etc"; break;
                case 5: slotname = "cash"; break;
            }

            MySQL_Connection.Instance.RunQuery(string.Format("UPDATE characters SET {0}_slots = {1} WHERE internal_id = {2}", slotname, newslots, pConnection.CharacterInternalID));

            pConnection.SendTimeUpdate();
        }

        public virtual void HandleInventoryUpdate(ClientConnection pConnection, MaplePacket pPacket)
        {
            CharacterInventory inventory = pConnection.CharData.Inventory;

            byte type1 = pPacket.ReadByte();
            byte items = pPacket.ReadByte();
            byte type3 = pPacket.ReadByte();
            if (type3 == 0) // Add or update item
            {
                for (var amnt = 0; amnt < items; amnt++)
                {
                    byte type4 = pPacket.ReadByte();
                    byte inv = pPacket.ReadByte();
                    short slot = pPacket.ReadShort();
                    inv -= 1;

                    if (type4 == 0) // New Item
                    {

                        ItemBase item = ItemBase.DecodeItemData(pConnection, pPacket);

                        if (inv == 0)
                        {
                            // Equip
                            byte internalInventory = CharacterInventory.GetEquipInventoryFromSlot(slot);
                            slot = CharacterInventory.CorrectEquipSlot(internalInventory, slot);

                            if (!inventory.EquipmentItems[internalInventory].ContainsKey(slot))
                                inventory.EquipmentItems[internalInventory].Add(slot, item as ItemEquip);
                            else
                                inventory.EquipmentItems[internalInventory][slot] = item as ItemEquip;
                        }
                        else
                        {
                            if (!inventory.InventoryItems[inv - 1].ContainsKey((byte)slot))
                                inventory.InventoryItems[inv - 1].Add((byte)slot, item);
                            else
                                inventory.InventoryItems[inv - 1][(byte)slot] = item;
                        }

                        using (InsertQueryBuilder itemsTable = new InsertQueryBuilder("items"))
                        {
                            itemsTable.OnDuplicateUpdate = true;
                            Queries.SaveItem(pConnection, inv, slot, item, itemsTable);
                            itemsTable.RunQuery();

                        }

                        if (item is ItemPet)
                        {
                            var pet = item as ItemPet;
                            using (InsertQueryBuilder petTable = new InsertQueryBuilder("pets"))
                            {
                                petTable.OnDuplicateUpdate = true;
                                Queries.SavePet(pConnection.CharacterInternalID, pet, petTable);
                                petTable.RunQuery();
                            }
                        }

                    }
                    else if (type4 == 1) // Update amount
                    {
                        short amount = pPacket.ReadShort();
                        if (inv == 0)
                        {
                            pConnection.Logger_WriteLine("WUTWUT"); // Should _never_ happen
                            continue;
                        }

                        ItemBase item = inventory.InventoryItems[inv - 1][(byte)slot];
                        item.Amount = amount;

                        AccountDataCache.Instance.SetChecksumOfSlot(pConnection.CharacterID, pConnection.WorldID, inv, slot, item.GetChecksum());

                        using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                        {
                            itemTable.SetColumn("amount", amount);
                            itemTable.SetColumn("checksum", item.GetChecksum());
                            itemTable.SetWhereColumn("inventory", inv);
                            itemTable.SetWhereColumn("slot", slot);
                            itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);

                            MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                        }


                    }
                    else if (type4 == 2) // Swap
                    {
                        short slotfrom = slot;
                        short slotto = pPacket.ReadShort();

                        bool founditem = false;

                        if (inv == 0)
                        {
                            // Equips!
                            byte internalInventoryFrom = CharacterInventory.GetEquipInventoryFromSlot(slotfrom);
                            byte internalInventoryTo = CharacterInventory.GetEquipInventoryFromSlot(slotto);
                            slotfrom = CharacterInventory.CorrectEquipSlot(internalInventoryFrom, slotfrom);
                            slotto = CharacterInventory.CorrectEquipSlot(internalInventoryTo, slotto);

                            // Switch Equips
                            ItemEquip item = inventory.EquipmentItems[internalInventoryFrom][slotfrom];
                            if (inventory.EquipmentItems[internalInventoryTo].ContainsKey(slotto))
                            {
                                inventory.EquipmentItems[internalInventoryFrom][slotfrom] =
                                    inventory.EquipmentItems[internalInventoryTo][slotto];

                                inventory.EquipmentItems[internalInventoryTo].Remove(slotto); // Remove item
                                founditem = true;
                            }
                            else
                            {
                                inventory.EquipmentItems[internalInventoryFrom].Remove(slotfrom);
                            }
                            inventory.EquipmentItems[internalInventoryTo].Add(slotto, item);
                        }
                        else
                        {
                            // Switch Items
                            ItemBase item = inventory.InventoryItems[inv - 1][(byte)slotfrom];
                            if (inventory.InventoryItems[inv - 1].ContainsKey((byte)slotto))
                            {
                                inventory.InventoryItems[inv - 1][(byte)slotfrom] =
                                    inventory.InventoryItems[inv - 1][(byte)slotto];
                                inventory.InventoryItems[inv - 1].Remove((byte)slotto); // Remove item
                                founditem = true;
                            }
                            else
                            {
                                inventory.InventoryItems[inv - 1].Remove((byte)slotfrom);
                            }
                            inventory.InventoryItems[inv - 1].Add((byte)slotto, item);
                        }

                        if (founditem) // New slot contained item
                        {
                            using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                            {
                                itemTable.SetColumn("slot", slotfrom + 3000);
                                itemTable.SetWhereColumn("inventory", inv);
                                itemTable.SetWhereColumn("slot", slotto);
                                itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);

                                MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                            }
                        }

                        using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                        {
                            itemTable.SetColumn("slot", slotto);
                            itemTable.SetWhereColumn("inventory", inv);
                            itemTable.SetWhereColumn("slot", slotfrom);
                            itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);

                            MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                        }

                        if (founditem) // Fix other slot
                        {
                            using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                            {
                                itemTable.SetColumn("slot", slotfrom);
                                itemTable.SetWhereColumn("inventory", inv);
                                itemTable.SetWhereColumn("slot", slotfrom + 3000);
                                itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);

                                MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                            }
                        }
                    }
                    else if (type4 == 3)
                    {
                        // Drop/delete item.

                        if (inv == 0)
                        {
                            // Equips!
                            byte internalInventory = CharacterInventory.GetEquipInventoryFromSlot(slot);
                            slot = CharacterInventory.CorrectEquipSlot(internalInventory, slot);

                            if (inventory.EquipmentItems[internalInventory].ContainsKey(slot))
                            {
                                inventory.EquipmentItems[internalInventory].Remove(slot);
                                AccountDataCache.Instance.DeleteItemChecksum(pConnection, 0, slot);
                            }
                            else
                                pConnection.Logger_WriteLine("!!! Could not find item @ {0} {1}", inv, slot);
                        }
                        else
                        {
                            if (inventory.InventoryItems[inv - 1].ContainsKey((byte)slot))
                            {
                                inventory.InventoryItems[inv - 1].Remove((byte)slot);
                                AccountDataCache.Instance.DeleteItemChecksum(pConnection, (ushort)(inv - 1), slot);
                            }
                            else
                                pConnection.Logger_WriteLine("!!! Could not find item @ {0} {1}", inv, slot);


                        }

                        using (DeleteQueryBuilder itemTable = new DeleteQueryBuilder("items"))
                        {
                            itemTable.SetWhereColumn("inventory", inv);
                            itemTable.SetWhereColumn("slot", slot);
                            itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);
                            itemTable.RunQuery();
                        }
                    }

                    else if (type4 == 4)
                    {
                        pPacket.ReadLong(); // Unknown..?
                    }
                    else if (type4 == 5)
                    {
                        // 'Swap' items from and to bags
                        inv -= 1;

                        short from = slot;
                        byte slotfrom = (byte)(from % 100);
                        byte bagfrom = (byte)(from / 100);

                        short to = pPacket.ReadShort();
                        byte slotto = (byte)(to % 100);
                        byte bagto = (byte)(to / 100);


                        slotfrom -= 1;
                        slotto -= 1;
                        if (bagto == 0)
                            bagto = 255;
                        else
                            bagto -= 1;
                        if (bagfrom == 0)
                            bagfrom = 255;
                        else
                            bagfrom -= 1;


                        ushort invto = bagto == 255 ? inv : GameHelper.GetBagID(bagto, inv);
                        ushort invfrom = bagfrom == 255 ? inv : GameHelper.GetBagID(bagfrom, inv);


                        if (
                            (bagfrom != 255 && bagto != 255) ||
                            (bagfrom == bagto) || // Check if the item is being moved to itself or something

                            (bagfrom == 255 && !inventory.InventoryItems[inv].ContainsKey(slotfrom)) ||
                            (bagfrom != 255 && (!inventory.BagItems.ContainsKey(invfrom) || !inventory.BagItems[invfrom].Items.ContainsKey(slotfrom))) ||

                            (bagto != 255 && !inventory.BagItems.ContainsKey(invto)) // Only check if bag exists
                            )
                        {
                            pConnection.Logger_WriteLine("Invalid item movement in bag !!!");
                            continue;
                        }

                        bool founditem = false;
                        if (bagfrom == 255)
                        {
                            // Move to bag
                            ItemBase ib = inventory.InventoryItems[inv][slotfrom];
                            if (inventory.BagItems[invto].Items.ContainsKey(slotto))
                            {
                                inventory.InventoryItems[inv][slotfrom] = inventory.BagItems[invto].Items[slotto];
                                inventory.BagItems[invto].Items.Remove(slotto);
                                founditem = true;
                            }

                            inventory.BagItems[invto].Items.Add(slotto, ib);



                        }
                        else
                        {
                            // Move to normal slot
                            ItemBase ib = inventory.BagItems[invfrom].Items[slotfrom];
                            if (inventory.InventoryItems[inv].ContainsKey(slotto))
                            {
                                inventory.BagItems[invfrom].Items[slotfrom] = inventory.InventoryItems[inv][slotto];
                                inventory.InventoryItems[inv].Remove(slotto);
                            }

                            inventory.InventoryItems[inv].Add(slotto, ib);



                            using (InsertQueryBuilder itemsTable = new InsertQueryBuilder("items"))
                            {
                                itemsTable.OnDuplicateUpdate = true;
                                Queries.SaveItem(pConnection, inv, slot, ib, itemsTable);
                                itemsTable.RunQuery();
                            }


                        }

                        /*
                         * Item A: item being used to move/swap | inv 3, slot 21 (Etc) | slotfrom, invfrom
                         * Item B: item that is being swapped with A | inv 11, slot 3 (Bag 2) | slotto, invto
                         * 
                         * Move B to a temp slot, to the new inventory: inv 11 -> inv 3, slot 3 -> slot 3021
                         * Move A to B: inv 3 -> inv 11, slot 21 -> slot 3
                         * Move B to A: slot 3021 -> slot 21
                         * 
                        */


                        if (founditem) // New slot contained item
                        {
                            // Temporary moving item
                            using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                            {
                                itemTable.SetColumn("slot", slotfrom + 3000);
                                itemTable.SetColumn("inventory", invfrom);
                                itemTable.SetWhereColumn("inventory", invto);
                                itemTable.SetWhereColumn("slot", slotto);
                                itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);

                                MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                            }
                        }

                        using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                        {
                            itemTable.SetColumn("slot", slotto);
                            itemTable.SetColumn("inventory", invto);
                            itemTable.SetWhereColumn("inventory", invfrom);
                            itemTable.SetWhereColumn("slot", slotfrom);
                            itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);

                            MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                        }

                        if (founditem) // Fix other slot
                        {
                            using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                            {
                                itemTable.SetColumn("slot", slotfrom);
                                itemTable.SetWhereColumn("inventory", invfrom);
                                itemTable.SetWhereColumn("slot", slotfrom + 3000);
                                itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);

                                MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                            }
                        }
                    }
                    else if (type4 == 6)
                    {
                        // Update bag item amount
                        inv -= 1;

                        short from = slot;
                        byte slotfrom = (byte)(from % 100);
                        byte bagfrom = (byte)(from / 100);

                        short amount = pPacket.ReadShort();


                        slotfrom -= 1;
                        if (bagfrom == 0)
                        {
                            pConnection.Logger_WriteLine("Invalid item bag!");
                            continue;
                        }
                        else
                            bagfrom -= 1;

                        ushort invfrom = GameHelper.GetBagID(bagfrom, inv);

                        if (
                            !inventory.BagItems.ContainsKey(invfrom) || !inventory.BagItems[invfrom].Items.ContainsKey(slotfrom)
                            )
                        {
                            pConnection.Logger_WriteLine("Invalid item movement in bag (item did not exist)!!!");
                            continue;
                        }



                        ItemBase item = inventory.BagItems[invfrom].Items[slotfrom];
                        item.Amount = amount;

                        AccountDataCache.Instance.SetChecksumOfSlot(pConnection.CharacterID, pConnection.WorldID, inv, slot, item.GetChecksum());

                        using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                        {
                            itemTable.SetColumn("amount", amount);
                            itemTable.SetColumn("checksum", item.GetChecksum());
                            itemTable.SetWhereColumn("inventory", invfrom);
                            itemTable.SetWhereColumn("slot", slot);
                            itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);

                            MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                        }
                    }
                    else if (type4 == 7)
                    {
                        // Delete/drop bag item D:
                        inv -= 1;

                        short from = slot;
                        byte slotfrom = (byte)(from % 100);
                        byte bagfrom = (byte)(from / 100);

                        slotfrom -= 1;
                        bagfrom -= 1;
                        ushort invfrom = GameHelper.GetBagID(bagfrom, inv);


                        using (DeleteQueryBuilder itemTable = new DeleteQueryBuilder("items"))
                        {
                            itemTable.SetWhereColumn("inventory", invfrom);
                            itemTable.SetWhereColumn("slot", slotfrom);
                            itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);

                            MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                        }
                    }
                    else if (type4 == 8)
                    {
                        // Swap/move item in bags
                        inv -= 1;

                        short from = slot;
                        byte slotfrom = (byte)(from % 100);
                        byte bagfrom = (byte)(from / 100);

                        short to = pPacket.ReadShort();
                        byte slotto = (byte)(to % 100);
                        byte bagto = (byte)(to / 100);

                        slotfrom -= 1;
                        slotto -= 1;
                        bagto -= 1;
                        bagfrom -= 1;


                        ushort invto = GameHelper.GetBagID(bagto, inv);
                        ushort invfrom = GameHelper.GetBagID(bagfrom, inv);


                        if (!inventory.BagItems.ContainsKey(invfrom) || !inventory.BagItems.ContainsKey(invto))
                        {
                            pConnection.Logger_WriteLine("Invalid item movement in bag");
                            continue;
                        }

                        if (!inventory.BagItems[invfrom].Items.ContainsKey(slotfrom))
                        {
                            pConnection.Logger_WriteLine("Invalid item movement in bag (item not found)");
                            continue;
                        }

                        ItemBase item = inventory.BagItems[invfrom].Items[slotfrom];

                        bool founditem = false;

                        if (inventory.BagItems[invto].Items.ContainsKey(slotto))
                        {
                            // Swap

                            inventory.BagItems[invfrom].Items[slotfrom] = inventory.BagItems[invto].Items[slotto];
                            inventory.BagItems[invto].Items.Remove(slotto); // Delete item
                            founditem = true;
                        }
                        inventory.BagItems[invto].Items.Add(slotto, item);




                        if (founditem) // New slot contained item
                        {
                            // Temporary moving item
                            using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                            {
                                itemTable.SetColumn("slot", slotfrom + 3000);
                                itemTable.SetColumn("inventory", invfrom);
                                itemTable.SetWhereColumn("inventory", invto);
                                itemTable.SetWhereColumn("slot", slotto);
                                itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);

                                MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                            }
                        }

                        using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                        {
                            itemTable.SetColumn("slot", slotto);
                            itemTable.SetColumn("inventory", invto);
                            itemTable.SetWhereColumn("inventory", invfrom);
                            itemTable.SetWhereColumn("slot", slotfrom);
                            itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);

                            MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                        }

                        if (founditem) // Fix other slot
                        {
                            using (UpdateQueryBuilder itemTable = new UpdateQueryBuilder("items"))
                            {
                                itemTable.SetColumn("slot", slotfrom);
                                itemTable.SetWhereColumn("inventory", invfrom);
                                itemTable.SetWhereColumn("slot", slotfrom + 3000);
                                itemTable.SetWhereColumn("character_id", pConnection.CharacterInternalID);

                                MySQL_Connection.Instance.RunQuery(itemTable.ToString());
                            }
                        }
                    }
                    else if (type4 == 9)
                    {
                        // Add item directly to bag
                        inv -= 1;

                        ItemBase item = ItemBase.DecodeItemData(pConnection, pPacket);

                        short from = slot;
                        byte slotfrom = (byte)(from % 100);
                        byte bagfrom = (byte)(from / 100);

                        slotfrom -= 1;
                        bagfrom -= 1;
                        ushort invfrom = GameHelper.GetBagID(bagfrom, inv);

                        if (!inventory.BagItems.ContainsKey(invfrom)) continue;

                        inventory.BagItems[invfrom].Items[slotfrom] = item;


                        using (InsertQueryBuilder itemsTable = new InsertQueryBuilder("items"))
                        {
                            itemsTable.OnDuplicateUpdate = true;
                            Queries.SaveItem(pConnection, invfrom, slotfrom, item, itemsTable);
                            itemsTable.RunQuery();

                        }
                        if (item is ItemPet)
                        {
                            var pet = item as ItemPet;
                            using (InsertQueryBuilder petTable = new InsertQueryBuilder("pets"))
                            {
                                petTable.OnDuplicateUpdate = true;
                                Queries.SavePet(pConnection.CharacterInternalID, pet, petTable);
                                petTable.RunQuery();
                            }
                        }
                    }
                    else if (type4 == 10)
                    {
                        pConnection.Logger_WriteLine("Player probably removed some bag item... O.o?");
                    }
                }
            }
            pConnection.SendTimeUpdate();
        }


        public virtual void HandleChangeMap(ClientConnection pConnection, MaplePacket pPacket)
        {

            int tmp = pPacket.ReadShort();
            pPacket.Skip(tmp * (4 + 4));

            int channelid = pPacket.ReadInt();
            pConnection.ChannelID = (byte)channelid;

#if LOCALE_EMS
            pPacket.ReadByte();
#endif
            pPacket.Skip(1 + 4);
            pPacket.Skip(1); // Portals taken
            pPacket.Skip(4);

            bool isConnecting = pPacket.ReadBool();

            if (!isConnecting && pConnection.CharData == null)
            {
                return;
            }

            pConnection._CharactersInMap.Clear();

            tmp = pPacket.ReadShort(); // Contains Message (Not used anymore lol.)
            if (tmp > 0)
            {
                pPacket.ReadString(); // Title
                for (int i = 0; i < tmp; i++)
                {
                    pPacket.ReadString(); // Line N
                }
            }

            if (isConnecting)
            {
                pPacket.Skip(12); // RNGs

                pConnection.Logger_WriteLine("--------- Started parsing Character Info ----------");

                CharacterData data = new CharacterData();
                data.Decode(pConnection, pPacket);

                pConnection.Logger_WriteLine("--------- Done parsing Character Info ----------");

                // Quick duplicate check
                Tuple<int, byte, byte, ushort> conflicted = null;
                using (var reader = MySQL_Connection.Instance.RunQuery("SELECT id, level, world_id, job FROM characters WHERE id <> " + data.Stats.ID + " AND name = " + MySQL_Connection.Escape(data.Stats.Name)) as MySql.Data.MySqlClient.MySqlDataReader)
                {
                    if (reader.Read())
                    {
                        // CONFLICTS
                        conflicted = new Tuple<int, byte, byte, ushort>(reader.GetInt32(0), reader.GetByte(1), reader.GetByte(2), reader.GetUInt16(3));
                    }
                }
                if (conflicted == null)
                {
                    if (!data.SaveData(pConnection))
                        return;

                    pConnection.CharData = data;

                    pConnection.Logger_WriteLine("--------- Saved parsed Character Info ----------");


                    pConnection.LastLoggedCharacterName = pConnection.CharData.Stats.Name;
                    pConnection.LastLoggedDate = pConnection.CharData.Stats.DateThing.ToString();

                    pConnection.LogFilename += "-" + pConnection.CharacterInternalID;

                    pConnection.LastExpPoint = (byte)EXPTable.GetLevelPercentage(data.Stats.Level, data.Stats.EXP);

                    pConnection.SendInfoText("Your character {0} has been saved!", pConnection.CharData.Stats.Name);

                    // Save SessionRestart Info
                    SessionRestartCache.Instance.StoreInfo(pConnection.IP, pConnection.MachineID, pConnection.CharacterID, pConnection.WorldID);

                }
                else
                {
                    pConnection.LogFilename += "-(CONFLICT)" + data.Stats.Name;

                    pConnection.Logger_WriteLine("!!!!!! FOUND CHARACTER NAME CONFLICT ! Expected Character ID {0}, found Character ID {1} in database!", data.Stats.ID, conflicted.Item1);
                    pConnection.Logger_WriteLine("Level diff: {0} - {1}", data.Stats.Level, conflicted.Item3);
                    pConnection.Logger_WriteLine("Job diff: {0} - {1}", data.Stats.JobID, conflicted.Item4);
                    pConnection.Logger_WriteLine("World diff: {0} - {1}", pConnection.WorldID, conflicted.Item2);
                    pConnection.SendInfoText("A different character has already this name! Delete this character via the website first!");
                }


                Queries.SaveServerIP(pConnection.ConnectedToIP, pConnection.ConnectedToPort, GameHelper.GetAllianceWorldID(pConnection.WorldID), pConnection.ChannelID);
            }
            else
            {
                pPacket.ReadByte();

                int mapid = pPacket.ReadInt();
                byte mappos = pPacket.ReadByte();
                pConnection.Logger_WriteLine("New MapID: {0} ({1})", mapid, mappos);

                pConnection.CharData.Stats.MapID = mapid;
                pConnection.CharData.Stats.MapPos = mappos;

                int hp = pPacket.ReadInt();
                pConnection.CharData.Stats.HP = hp;

                if (pPacket.ReadBool())
                {
                    pPacket.ReadInt();
                    pPacket.ReadInt();
                }

                MySQL_Connection.Instance.RunQuery(string.Format("UPDATE characters SET chp = {0}, map = {1}, pos = {2} WHERE internal_id = {3}", hp, mapid, mappos, pConnection.CharacterInternalID));
            }

            pPacket.ReadLong();
            //DateTime servertime = DateTime.FromFileTime(pPacket.ReadLong());
            pPacket.ReadInt(); // 100?
            pPacket.ReadByte(); // 0
            pPacket.ReadByte(); // 0
            pPacket.ReadByte(); // 1

            if (pPacket.Position != pPacket.Length)
            {
                pConnection.Logger_WriteLine("Data not fully read. Halp.: {0} of {1} read", pPacket.Position, pPacket.Length);
            }


            pConnection.SendTimeUpdate();
        }


        public virtual void HandleKeymap(ClientConnection pConnection, MaplePacket pPacket)
        {
            byte mode = pPacket.ReadByte();
            if (mode == 0)
            {
                // Keymap
                if (pPacket.Length - pPacket.Position != (1 + 4) * ServerMapleInfo.KEYMAP_SLOTS)
                {
                    pConnection.Logger_ErrorLog("Keymap size not correct. {0} != {1}", pPacket.Length - pPacket.Position, (1 + 4) * ServerMapleInfo.KEYMAP_SLOTS);
                    return;
                }

                StringBuilder sb = new StringBuilder();
                sb.Append("DELETE FROM character_keymaps WHERE character_id = " + pConnection.CharacterInternalID + ";");
                sb.Append("INSERT INTO character_keymaps VALUES (" + pConnection.CharacterInternalID);
                for (int i = 0; i < ServerMapleInfo.KEYMAP_SLOTS; i++)
                    sb.Append("," + pPacket.ReadByte() + "," + pPacket.ReadInt());

                sb.Append(");");

                MySQL_Connection.Instance.RunQuery(sb.ToString());

                pConnection.Logger_WriteLine("Saved keymap!");
            }
        }

    }
}
