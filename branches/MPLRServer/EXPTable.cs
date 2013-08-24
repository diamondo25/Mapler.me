﻿using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    public class EXPTable
    {
        public static Dictionary<byte, long> EXP { get; private set; }

        public static void Load()
        {
            EXP = new Dictionary<byte, long>();
            EXP.Add(0, 0);
            EXP.Add(1, 15);
            EXP.Add(2, 34);
            EXP.Add(3, 57);
            EXP.Add(4, 92);
            EXP.Add(5, 135);
            EXP.Add(6, 372);
            EXP.Add(7, 560);
            EXP.Add(8, 840);
            EXP.Add(9, 1242);
            EXP.Add(10, 1242);
            EXP.Add(11, 1242);
            EXP.Add(12, 1242);
            EXP.Add(13, 1242);
            EXP.Add(14, 1242);
            EXP.Add(15, 1490);
            EXP.Add(16, 1788);
            EXP.Add(17, 2145);
            EXP.Add(18, 2574);
            EXP.Add(19, 3088);
            EXP.Add(20, 3705);
            EXP.Add(21, 4446);
            EXP.Add(22, 5335);
            EXP.Add(23, 6402);
            EXP.Add(24, 7682);
            EXP.Add(25, 9218);
            EXP.Add(26, 11061);
            EXP.Add(27, 13273);
            EXP.Add(28, 15927);
            EXP.Add(29, 19112);
            EXP.Add(30, 19112);
            EXP.Add(31, 19112);
            EXP.Add(32, 19112);
            EXP.Add(33, 19112);
            EXP.Add(34, 19112);
            EXP.Add(35, 22934);
            EXP.Add(36, 27520);
            EXP.Add(37, 33024);
            EXP.Add(38, 39628);
            EXP.Add(39, 47553);
            EXP.Add(40, 51357);
            EXP.Add(41, 55465);
            EXP.Add(42, 59902);
            EXP.Add(43, 64694);
            EXP.Add(44, 69869);
            EXP.Add(45, 75458);
            EXP.Add(46, 81494);
            EXP.Add(47, 88013);
            EXP.Add(48, 95054);
            EXP.Add(49, 102658);
            EXP.Add(50, 110870);
            EXP.Add(51, 119739);
            EXP.Add(52, 129318);
            EXP.Add(53, 139663);
            EXP.Add(54, 150836);
            EXP.Add(55, 162902);
            EXP.Add(56, 175934);
            EXP.Add(57, 190008);
            EXP.Add(58, 205208);
            EXP.Add(59, 221624);
            EXP.Add(60, 221624);
            EXP.Add(61, 221624);
            EXP.Add(62, 221624);
            EXP.Add(63, 221624);
            EXP.Add(64, 221624);
            EXP.Add(65, 239353);
            EXP.Add(66, 258501);
            EXP.Add(67, 279181);
            EXP.Add(68, 301515);
            EXP.Add(69, 325636);
            EXP.Add(70, 351686);
            EXP.Add(71, 379820);
            EXP.Add(72, 410205);
            EXP.Add(73, 443021);
            EXP.Add(74, 478462);
            EXP.Add(75, 511954);
            EXP.Add(76, 547790);
            EXP.Add(77, 586135);
            EXP.Add(78, 627164);
            EXP.Add(79, 671065);
            EXP.Add(80, 718039);
            EXP.Add(81, 768301);
            EXP.Add(82, 822082);
            EXP.Add(83, 879627);
            EXP.Add(84, 941200);
            EXP.Add(85, 1007084);
            EXP.Add(86, 1077579);
            EXP.Add(87, 1153009);
            EXP.Add(88, 1233719);
            EXP.Add(89, 1320079);
            EXP.Add(90, 1412484);
            EXP.Add(91, 1511357);
            EXP.Add(92, 1617151);
            EXP.Add(93, 1730351);
            EXP.Add(94, 1851475);
            EXP.Add(95, 1981078);
            EXP.Add(96, 2119753);
            EXP.Add(97, 2268135);
            EXP.Add(98, 2426904);
            EXP.Add(99, 2596787);
            EXP.Add(100, 2596787);
            EXP.Add(101, 2596787);
            EXP.Add(102, 2596787);
            EXP.Add(103, 2596787);
            EXP.Add(104, 2596787);
            EXP.Add(105, 2778562);
            EXP.Add(106, 2973061);
            EXP.Add(107, 3181175);
            EXP.Add(108, 3403857);
            EXP.Add(109, 3642126);
            EXP.Add(110, 3897074);
            EXP.Add(111, 4169869);
            EXP.Add(112, 4461759);
            EXP.Add(113, 4774082);
            EXP.Add(114, 5108267);
            EXP.Add(115, 5465845);
            EXP.Add(116, 5848454);
            EXP.Add(117, 6257845);
            EXP.Add(118, 6695894);
            EXP.Add(119, 7164606);
            EXP.Add(120, 7666128);
            EXP.Add(121, 8202756);
            EXP.Add(122, 8776948);
            EXP.Add(123, 9391334);
            EXP.Add(124, 10048727);
            EXP.Add(125, 10752137);
            EXP.Add(126, 11504786);
            EXP.Add(127, 12310121);
            EXP.Add(128, 13171829);
            EXP.Add(129, 14093857);
            EXP.Add(130, 15080426);
            EXP.Add(131, 16136055);
            EXP.Add(132, 17265578);
            EXP.Add(133, 18474168);
            EXP.Add(134, 19767359);
            EXP.Add(135, 21151074);
            EXP.Add(136, 22631649);
            EXP.Add(137, 24215864);
            EXP.Add(138, 25910974);
            EXP.Add(139, 27724742);
            EXP.Add(140, 29665473);
            EXP.Add(141, 31742056);
            EXP.Add(142, 33963999);
            EXP.Add(143, 36341478);
            EXP.Add(144, 38885381);
            EXP.Add(145, 41607357);
            EXP.Add(146, 44519871);
            EXP.Add(147, 47636261);
            EXP.Add(148, 50970799);
            EXP.Add(149, 54538754);
            EXP.Add(150, 58356466);
            EXP.Add(151, 62441418);
            EXP.Add(152, 66812317);
            EXP.Add(153, 71489179);
            EXP.Add(154, 76493421);
            EXP.Add(155, 81847960);
            EXP.Add(156, 87577317);
            EXP.Add(157, 93707729);
            EXP.Add(158, 100267270);
            EXP.Add(159, 107285978);
            EXP.Add(160, 113723136);
            EXP.Add(161, 120546524);
            EXP.Add(162, 127779315);
            EXP.Add(163, 135446073);
            EXP.Add(164, 143572837);
            EXP.Add(165, 152187207);
            EXP.Add(166, 161318439);
            EXP.Add(167, 170997545);
            EXP.Add(168, 181257397);
            EXP.Add(169, 192132840);
            EXP.Add(170, 203660810);
            EXP.Add(171, 215880458);
            EXP.Add(172, 228833285);
            EXP.Add(173, 242563282);
            EXP.Add(174, 257117078);
            EXP.Add(175, 272544102);
            EXP.Add(176, 288896748);
            EXP.Add(177, 306230552);
            EXP.Add(178, 324604385);
            EXP.Add(179, 344080648);
            EXP.Add(180, 364725486);
            EXP.Add(181, 386609015);
            EXP.Add(182, 409805555);
            EXP.Add(183, 434393888);
            EXP.Add(184, 460457521);
            EXP.Add(185, 488084972);
            EXP.Add(186, 517370070);
            EXP.Add(187, 548412274);
            EXP.Add(188, 581317010);
            EXP.Add(189, 616196030);
            EXP.Add(190, 653167791);
            EXP.Add(191, 692357858);
            EXP.Add(192, 733899329);
            EXP.Add(193, 777933288);
            EXP.Add(194, 824609285);
            EXP.Add(195, 874085842);
            EXP.Add(196, 926530992);
            EXP.Add(197, 982122851);
            EXP.Add(198, 1041050222);
            EXP.Add(199, 1103513235);
            EXP.Add(200, 2207026470);
            EXP.Add(201, 2648431764);
            EXP.Add(202, 3178118116);
            EXP.Add(203, 3813741739);
            EXP.Add(204, 4576490086);
            EXP.Add(205, 5491788103);
            EXP.Add(206, 6590145723);
            EXP.Add(207, 7908174867);
            EXP.Add(208, 9489809840);
            EXP.Add(209, 11387771808);
            EXP.Add(210, 24142076232);
            EXP.Add(211, 25590600805);
            EXP.Add(212, 27126036853);
            EXP.Add(213, 28753599064);
            EXP.Add(214, 30478815007);
            EXP.Add(215, 32307543907);
            EXP.Add(216, 34245996541);
            EXP.Add(217, 36300756333);
            EXP.Add(218, 38478801712);
            EXP.Add(219, 40787529814);
            EXP.Add(220, 84838062013);
            EXP.Add(221, 88231584493);
            EXP.Add(222, 91760847872);
            EXP.Add(223, 95431281786);
            EXP.Add(224, 99248533057);
            EXP.Add(225, 103218474379);
            EXP.Add(226, 107347213354);
            EXP.Add(227, 111641101888);
            EXP.Add(228, 116106745963);
            EXP.Add(229, 120751015801);
            EXP.Add(230, 246332072234);
            EXP.Add(231, 251258713678);
            EXP.Add(232, 256283887951);
            EXP.Add(233, 261409565710);
            EXP.Add(234, 266637757024);
            EXP.Add(235, 271970512164);
            EXP.Add(236, 277409922407);
            EXP.Add(237, 282958120855);
            EXP.Add(238, 288617283272);
            EXP.Add(239, 294389628937);
            EXP.Add(240, 594667050452);
            EXP.Add(241, 600613720956);
            EXP.Add(242, 606619858165);
            EXP.Add(243, 612686056746);
            EXP.Add(244, 618812917313);
            EXP.Add(245, 625001046486);
            EXP.Add(246, 631251056950);
            EXP.Add(247, 637563567519);
            EXP.Add(248, 643939203194);
            EXP.Add(249, 650378595225);
        }

        public static float GetLevelPercentage(byte pLevel, long pEXP)
        {
            if (pLevel <= 0 || pLevel >= 250) return 0.0f;
            return (float)(((float)pEXP / (float)EXP[pLevel]) * 100.0f);
        }
    }
}