<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = [
            ["provider_id" => 1, "name" => "AFGHANISTAN", "code" => "AFG", "code2" => "AF" ],
            ["provider_id" => 1, "name" => "ALAND ISLANDS", "code" => "ALA", "code2" => "AX" ],
            ["provider_id" => 1, "name" => "ALBANIA", "code" => "ALB", "code2" => "AL" ],
            ["provider_id" => 1, "name" => "ALGERIA", "code" => "DZA", "code2" => "DZ" ],
            ["provider_id" => 1, "name" => "AMERICAN SAMOA", "code" => "ASM", "code2" => "AS" ],
            ["provider_id" => 1, "name" => "ANDORRA", "code" => "AND", "code2" => "AD" ],
            ["provider_id" => 1, "name" => "ANGOLA", "code" => "AGO", "code2" => "AO" ],
            ["provider_id" => 1, "name" => "ANGUILLA", "code" => "AIA", "code2" => "AI" ],
            ["provider_id" => 1, "name" => "ANTARCTICA", "code" => "ATA", "code2" => "AQ" ],
            ["provider_id" => 1, "name" => "ANTIGUA AND BARBUDA", "code" => "ATG", "code2" => "AG" ],
            ["provider_id" => 1, "name" => "ARGENTINA", "code" => "ARG", "code2" => "AR" ],
            ["provider_id" => 1, "name" => "ARMENIA", "code" => "ARM", "code2" => "AM" ],
            ["provider_id" => 1, "name" => "ARUBA", "code" => "ABW", "code2" => "AW" ],
            ["provider_id" => 1, "name" => "AUSTRALIA", "code" => "AUS", "code2" => "AU" ],
            ["provider_id" => 1, "name" => "AUSTRIA", "code" => "AUT", "code2" => "AT" ],
            ["provider_id" => 1, "name" => "AZERBAIJAN", "code" => "AZE", "code2" => "AZ" ],
            ["provider_id" => 1, "name" => "BAHAMAS", "code" => "BHS", "code2" => "BS" ],
            ["provider_id" => 1, "name" => "BAHRAIN", "code" => "BHR", "code2" => "BH" ],
            ["provider_id" => 1, "name" => "BANGLADESH", "code" => "BGD", "code2" => "BD" ],
            ["provider_id" => 1, "name" => "BARBADOS", "code" => "BRB", "code2" => "BB" ],
            ["provider_id" => 1, "name" => "BELARUS", "code" => "BLR", "code2" => "BY" ],
            ["provider_id" => 1, "name" => "BELGIUM", "code" => "BEL", "code2" => "BE" ],
            ["provider_id" => 1, "name" => "BELIZE", "code" => "BLZ", "code2" => "BZ" ],
            ["provider_id" => 1, "name" => "BENIN", "code" => "BEN", "code2" => "BJ" ],
            ["provider_id" => 1, "name" => "BERMUDA", "code" => "BMU", "code2" => "BM" ],
            ["provider_id" => 1, "name" => "BHUTAN", "code" => "BTN", "code2" => "BT" ],
            ["provider_id" => 1, "name" => "BOLIVIA", "code" => "BOL", "code2" => "BO" ],
            ["provider_id" => 1, "name" => "BONAIRE, SINT EUSTATIUS AND SABA", "code" => "BES", "code2" => "BQ" ],
            ["provider_id" => 1, "name" => "BOSNIA AND HERZEGOVINA", "code" => "BIH", "code2" => "BA" ],
            ["provider_id" => 1, "name" => "BOTSWANA", "code" => "BWA", "code2" => "BW" ],
            ["provider_id" => 1, "name" => "BOUVET ISLAND", "code" => "BVT", "code2" => "BV" ],
            ["provider_id" => 1, "name" => "BRAZIL", "code" => "BRA", "code2" => "BR" ],
            ["provider_id" => 1, "name" => "BRITISH INDIAN OCEAN TERRITORY", "code" => "IOT", "code2" => "IO" ],
            ["provider_id" => 1, "name" => "BRUNEI DARUSSALAM", "code" => "BRN", "code2" => "BN" ],
            ["provider_id" => 1, "name" => "BULGARIA", "code" => "BGR", "code2" => "BG" ],
            ["provider_id" => 1, "name" => "BURKINA FASO", "code" => "BFA", "code2" => "BF" ],
            ["provider_id" => 1, "name" => "BURUNDI", "code" => "BDI", "code2" => "BI" ],
            ["provider_id" => 1, "name" => "CAMBODIA", "code" => "KHM", "code2" => "KH" ],
            ["provider_id" => 1, "name" => "CAMEROON", "code" => "CMR", "code2" => "CM" ],
            ["provider_id" => 1, "name" => "CANADA", "code" => "CAN", "code2" => "CA" ],
            ["provider_id" => 1, "name" => "CAPE VERDE", "code" => "CPV", "code2" => "CV" ],
            ["provider_id" => 1, "name" => "CAYMAN ISLANDS", "code" => "CYM", "code2" => "KY" ],
            ["provider_id" => 1, "name" => "CENTRAL AFRICAN REPUBLIC", "code" => "CAF", "code2" => "CF" ],
            ["provider_id" => 1, "name" => "CHAD", "code" => "TCD", "code2" => "TD" ],
            ["provider_id" => 1, "name" => "CHILE", "code" => "CHL", "code2" => "CL" ],
            ["provider_id" => 1, "name" => "CHINA", "code" => "CHN", "code2" => "CN" ],
            ["provider_id" => 1, "name" => "CHRISTMAS ISLAND", "code" => "CXR", "code2" => "CX" ],
            ["provider_id" => 1, "name" => "COCOS (KEELING) ISLANDS", "code" => "CCK", "code2" => "CC" ],
            ["provider_id" => 1, "name" => "COLOMBIA", "code" => "COL", "code2" => "CO" ],
            ["provider_id" => 1, "name" => "COMOROS", "code" => "COM", "code2" => "KM" ],
            ["provider_id" => 1, "name" => "CONGO", "code" => "COG", "code2" => "CG" ],
            ["provider_id" => 1, "name" => "CONGO, THE DEMOCRATIC REPUBLIC OF THE", "code" => "COD", "code2" => "CK" ],
            ["provider_id" => 1, "name" => "COOK ISLANDS", "code" => "COK", "code2" => "CR" ],
            ["provider_id" => 1, "name" => "COSTA RICA", "code" => "CRI", "code2" => "CI" ],
            ["provider_id" => 1, "name" => "COTE D'IVOIRE", "code" => "CIV", "code2" => "HR" ],
            ["provider_id" => 1, "name" => "CROATIA", "code" => "HRV", "code2" => "CU" ],
            ["provider_id" => 1, "name" => "CUBA", "code" => "CUB", "code2" => "CW" ],
            ["provider_id" => 1, "name" => "CURACAO", "code" => "CUW", "code2" => "CY" ],
            ["provider_id" => 1, "name" => "CYPRUS", "code" => "CYP", "code2" => "CZ" ],
            ["provider_id" => 1, "name" => "CZECH REPUBLIC", "code" => "CZE", "code2" => "DK" ],
            ["provider_id" => 1, "name" => "DENMARK", "code" => "DNK", "code2" => "DJ" ],
            ["provider_id" => 1, "name" => "DJIBOUTI", "code" => "DJI", "code2" => "DM" ],
            ["provider_id" => 1, "name" => "DOMINICA", "code" => "DMA", "code2" => "DO" ],
            ["provider_id" => 1, "name" => "DOMINICAN REPUBLIC", "code" => "DOM", "code2" => "TP" ],
            ["provider_id" => 1, "name" => "EAST TIMOR", "code" => "TMP", "code2" => "EC" ],
            ["provider_id" => 1, "name" => "ECUADOR", "code" => "ECU", "code2" => "EG" ],
            ["provider_id" => 1, "name" => "EGYPT", "code" => "EGY", "code2" => "SV" ],
            ["provider_id" => 1, "name" => "EL SALVADOR", "code" => "SLV", "code2" => "GQ" ],
            ["provider_id" => 1, "name" => "EQUATORIAL GUINEA", "code" => "GNQ", "code2" => "ER" ],
            ["provider_id" => 1, "name" => "ERITREA", "code" => "ERI", "code2" => "EE" ],
            ["provider_id" => 1, "name" => "ESTONIA", "code" => "EST", "code2" => "ET" ],
            ["provider_id" => 1, "name" => "ETHIOPIA", "code" => "ETH", "code2" => "FK" ],
            ["provider_id" => 1, "name" => "EUROPE ", "code" => "ULL", "code2" => "FO" ],
            ["provider_id" => 1, "name" => "FALKLAND ISLANDS (MALVINAS)", "code" => "FLK", "code2" => "FJ" ],
            ["provider_id" => 1, "name" => "FAROE ISLANDS", "code" => "FRO", "code2" => "FI" ],
            ["provider_id" => 1, "name" => "FIJI", "code" => "FJI", "code2" => "FR" ],
            ["provider_id" => 1, "name" => "FINLAND", "code" => "FIN", "code2" => "FR" ],
            ["provider_id" => 1, "name" => "FRANCE", "code" => "FRA", "code2" => "GF" ],
            ["provider_id" => 1, "name" => "FRENCH GUIANA", "code" => "GUF", "code2" => "PF" ],
            ["provider_id" => 1, "name" => "FRENCH POLYNESIA", "code" => "PYF", "code2" => "TF" ],
            ["provider_id" => 1, "name" => "FRENCH SOUTHERN TERRITORIES", "code" => "ATF", "code2" => "GA" ],
            ["provider_id" => 1, "name" => "GABON", "code" => "GAB", "code2" => "GM" ],
            ["provider_id" => 1, "name" => "GAMBIA", "code" => "GMB", "code2" => "GE" ],
            ["provider_id" => 1, "name" => "GEORGIA", "code" => "GEO", "code2" => "DE" ],
            ["provider_id" => 1, "name" => "GERMANY", "code" => "DEU", "code2" => "GH" ],
            ["provider_id" => 1, "name" => "GHANA", "code" => "GHA", "code2" => "GI" ],
            ["provider_id" => 1, "name" => "GIBRALTAR", "code" => "GIB", "code2" => "GR" ],
            ["provider_id" => 1, "name" => "GREECE", "code" => "GRC", "code2" => "GL" ],
            ["provider_id" => 1, "name" => "GREENLAND", "code" => "GRL", "code2" => "GD" ],
            ["provider_id" => 1, "name" => "GRENADA", "code" => "GRD", "code2" => "GP" ],
            ["provider_id" => 1, "name" => "GUADELOUPE", "code" => "GLP", "code2" => "GU" ],
            ["provider_id" => 1, "name" => "GUAM", "code" => "GUM", "code2" => "GT" ],
            ["provider_id" => 1, "name" => "GUATEMALA", "code" => "GTM", "code2" => "GG" ],
            ["provider_id" => 1, "name" => "GUERNSEY", "code" => "GGY", "code2" => "GN" ],
            ["provider_id" => 1, "name" => "GUINEA", "code" => "GIN", "code2" => "GW" ],
            ["provider_id" => 1, "name" => "GUINEA-BISSAU", "code" => "GNB", "code2" => "GY" ],
            ["provider_id" => 1, "name" => "GUYANA", "code" => "GUY", "code2" => "HT" ],
            ["provider_id" => 1, "name" => "HAITI", "code" => "HTI", "code2" => "HM" ],
            ["provider_id" => 1, "name" => "HEARD AND MCDONALD ISLANDS", "code" => "HMD", "code2" => "HN" ],
            ["provider_id" => 1, "name" => "HOLY SEE (VATICAN CITY STATE)", "code" => "VAT", "code2" => "HK" ],
            ["provider_id" => 1, "name" => "HONDURAS", "code" => "HND", "code2" => "HU" ],
            ["provider_id" => 1, "name" => "HONG KONG", "code" => "HKG", "code2" => "IS" ],
            ["provider_id" => 1, "name" => "HUNGARY", "code" => "HUN", "code2" => "IN" ],
            ["provider_id" => 1, "name" => "ICELAND", "code" => "ISL", "code2" => "ID" ],
            ["provider_id" => 1, "name" => "INDIA", "code" => "IND", "code2" => "IR" ],
            ["provider_id" => 1, "name" => "INDONESIA", "code" => "IDN", "code2" => "IQ" ],
            ["provider_id" => 1, "name" => "IRAN (ISLAMIC REPUBLIC OF)", "code" => "IRN", "code2" => "IE" ],
            ["provider_id" => 1, "name" => "IRAQ", "code" => "IRQ", "code2" => "IM" ],
            ["provider_id" => 1, "name" => "IRELAND", "code" => "IRL", "code2" => "IL" ],
            ["provider_id" => 1, "name" => "ISLE OF MAN", "code" => "IMN", "code2" => "IT" ],
            ["provider_id" => 1, "name" => "ISRAEL", "code" => "ISR", "code2" => "JM" ],
            ["provider_id" => 1, "name" => "ITALY", "code" => "ITA", "code2" => "JP" ],
            ["provider_id" => 1, "name" => "JAMAICA", "code" => "JAM", "code2" => "JE" ],
            ["provider_id" => 1, "name" => "JAPAN", "code" => "JPN", "code2" => "JO" ],
            ["provider_id" => 1, "name" => "JERSEY", "code" => "JEY", "code2" => "KZ" ],
            ["provider_id" => 1, "name" => "JORDAN", "code" => "JOR", "code2" => "KE" ],
            ["provider_id" => 1, "name" => "KAZAKHSTAN", "code" => "KAZ", "code2" => "KI" ],
            ["provider_id" => 1, "name" => "KENYA", "code" => "KEN", "code2" => "KP" ],
            ["provider_id" => 1, "name" => "KIRIBATI", "code" => "KIR", "code2" => "KR" ],
            ["provider_id" => 1, "name" => "KOREA, DEMOCRATIC PEOPLE'S REPUBLIC OF", "code" => "PRK", "code2" => "KW" ],
            ["provider_id" => 1, "name" => "KOREA, REPUBLIC OF", "code" => "KOR", "code2" => "KG" ],
            ["provider_id" => 1, "name" => "KOSOVO", "code" => "KOS", "code2" => "LA" ],
            ["provider_id" => 1, "name" => "KUWAIT", "code" => "KWT", "code2" => "LV" ],
            ["provider_id" => 1, "name" => "KYRGYZSTAN", "code" => "KGZ", "code2" => "LB" ],
            ["provider_id" => 1, "name" => "LAO PEOPLE'S DEMOCRATIC REPUBLIC", "code" => "LAO", "code2" => "LS" ],
            ["provider_id" => 1, "name" => "LATIN AMERICA ", "code" => "ULL", "code2" => "LR" ],
            ["provider_id" => 1, "name" => "LATVIA", "code" => "LVA", "code2" => "LY" ],
            ["provider_id" => 1, "name" => "LEBANON", "code" => "LBN", "code2" => "LI" ],
            ["provider_id" => 1, "name" => "LESOTHO", "code" => "LSO", "code2" => "LT" ],
            ["provider_id" => 1, "name" => "LIBERIA", "code" => "LBR", "code2" => "LU" ],
            ["provider_id" => 1, "name" => "LIBYAN ARAB JAMAHIRIYA", "code" => "LBY", "code2" => "MO" ],
            ["provider_id" => 1, "name" => "LIECHTENSTEIN", "code" => "LIE", "code2" => "MK" ],
            ["provider_id" => 1, "name" => "LITHUANIA", "code" => "LTU", "code2" => "MG" ],
            ["provider_id" => 1, "name" => "LUXEMBOURG", "code" => "LUX", "code2" => "MW" ],
            ["provider_id" => 1, "name" => "MACAU", "code" => "MAC", "code2" => "MY" ],
            ["provider_id" => 1, "name" => "MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF", "code" => "MKD", "code2" => "MV" ],
            ["provider_id" => 1, "name" => "MADAGASCAR", "code" => "MDG", "code2" => "ML" ],
            ["provider_id" => 1, "name" => "MALAWI", "code" => "MWI", "code2" => "MT" ],
            ["provider_id" => 1, "name" => "MALAYSIA", "code" => "MYS", "code2" => "MH" ],
            ["provider_id" => 1, "name" => "MALDIVES", "code" => "MDV", "code2" => "MQ" ],
            ["provider_id" => 1, "name" => "MALI", "code" => "MLI", "code2" => "MR" ],
            ["provider_id" => 1, "name" => "MALTA", "code" => "MLT", "code2" => "MU" ],
            ["provider_id" => 1, "name" => "MARSHALL ISLANDS", "code" => "MHL", "code2" => "YT" ],
            ["provider_id" => 1, "name" => "MARTINIQUE", "code" => "MTQ", "code2" => "MX" ],
            ["provider_id" => 1, "name" => "MAURITANIA", "code" => "MRT", "code2" => "FM" ],
            ["provider_id" => 1, "name" => "MAURITIUS", "code" => "MUS", "code2" => "MD" ],
            ["provider_id" => 1, "name" => "MAYOTTE", "code" => "MYT", "code2" => "MC" ],
            ["provider_id" => 1, "name" => "MEXICO", "code" => "MEX", "code2" => "MN" ],
            ["provider_id" => 1, "name" => "MICRONESIA, FEDERATED STATES OF", "code" => "FSM", "code2" => "ME" ],
            ["provider_id" => 1, "name" => "MOLDOVA, REPUBLIC OF", "code" => "MDA", "code2" => "MS" ],
            ["provider_id" => 1, "name" => "MONACO", "code" => "MCO", "code2" => "MA" ],
            ["provider_id" => 1, "name" => "MONGOLIA", "code" => "MNG", "code2" => "MZ" ],
            ["provider_id" => 1, "name" => "MONTENEGRO", "code" => "MNE", "code2" => "MM" ],
            ["provider_id" => 1, "name" => "MONTSERRAT", "code" => "MSR", "code2" => "NA" ],
            ["provider_id" => 1, "name" => "MOROCCO", "code" => "MAR", "code2" => "NR" ],
            ["provider_id" => 1, "name" => "MOZAMBIQUE", "code" => "MOZ", "code2" => "NP" ],
            ["provider_id" => 1, "name" => "MYANMAR", "code" => "MMR", "code2" => "NL" ],
            ["provider_id" => 1, "name" => "NAMIBIA", "code" => "NAM", "code2" => "AN" ],
            ["provider_id" => 1, "name" => "NAURU", "code" => "NRU", "code2" => "NC" ],
            ["provider_id" => 1, "name" => "NEPAL", "code" => "NPL", "code2" => "NZ" ],
            ["provider_id" => 1, "name" => "NETHERLANDS, THE", "code" => "NLD", "code2" => "NI" ],
            ["provider_id" => 1, "name" => "NEW CALEDONIA", "code" => "NCL", "code2" => "NE" ],
            ["provider_id" => 1, "name" => "NEW ZEALAND", "code" => "NZL", "code2" => "NG" ],
            ["provider_id" => 1, "name" => "NICARAGUA", "code" => "NIC", "code2" => "NU" ],
            ["provider_id" => 1, "name" => "NIGER", "code" => "NER", "code2" => "NF" ],
            ["provider_id" => 1, "name" => "NIGERIA", "code" => "NGA", "code2" => "MP" ],
            ["provider_id" => 1, "name" => "NIUE", "code" => "NIU", "code2" => "NO" ],
            ["provider_id" => 1, "name" => "NORFOLK ISLAND", "code" => "NFK", "code2" => "OM" ],
            ["provider_id" => 1, "name" => "NORTHERN MARIANA ISLANDS", "code" => "MNP", "code2" => "PK" ],
            ["provider_id" => 1, "name" => "NORWAY", "code" => "NOR", "code2" => "PW" ],
            ["provider_id" => 1, "name" => "OMAN", "code" => "OMN", "code2" => "PS" ],
            ["provider_id" => 1, "name" => "PAKISTAN", "code" => "PAK", "code2" => "PA" ],
            ["provider_id" => 1, "name" => "PALAU", "code" => "PLW", "code2" => "PG" ],
            ["provider_id" => 1, "name" => "PALESTINE", "code" => "PSE", "code2" => "PY" ],
            ["provider_id" => 1, "name" => "PANAMA", "code" => "PAN", "code2" => "PE" ],
            ["provider_id" => 1, "name" => "PAPUA NEW GUINEA", "code" => "PNG", "code2" => "PH" ],
            ["provider_id" => 1, "name" => "PARAGUAY", "code" => "PRY", "code2" => "PN" ],
            ["provider_id" => 1, "name" => "PERU", "code" => "PER", "code2" => "PL" ],
            ["provider_id" => 1, "name" => "PHILIPPINES", "code" => "PHL", "code2" => "PT" ],
            ["provider_id" => 1, "name" => "PITCAIRN", "code" => "PCN", "code2" => "PR" ],
            ["provider_id" => 1, "name" => "POLAND", "code" => "POL", "code2" => "QA" ],
            ["provider_id" => 1, "name" => "PORTUGAL", "code" => "PRT", "code2" => "RE" ],
            ["provider_id" => 1, "name" => "PUERTO RICO", "code" => "PRI", "code2" => "RO" ],
            ["provider_id" => 1, "name" => "QATAR", "code" => "QAT", "code2" => "RU" ],
            ["provider_id" => 1, "name" => "REUNION", "code" => "REU", "code2" => "RW" ],
            ["provider_id" => 1, "name" => "ROMANIA", "code" => "ROU", "code2" => "BL" ],
            ["provider_id" => 1, "name" => "RUSSIA", "code" => "RUS", "code2" => "KN" ],
            ["provider_id" => 1, "name" => "RWANDA", "code" => "RWA", "code2" => "LC" ],
            ["provider_id" => 1, "name" => "SAINT BARTHÉLEMY", "code" => "BLM", "code2" => "MF" ],
            ["provider_id" => 1, "name" => "SAINT HELENA, ASCENSION AND TRISTAN DA CUNHA", "code" => "SHN", "code2" => "VC" ],
            ["provider_id" => 1, "name" => "SAINT KITTS AND NEVIS", "code" => "KNA", "code2" => "WS" ],
            ["provider_id" => 1, "name" => "SAINT LUCIA", "code" => "LCA", "code2" => "SM" ],
            ["provider_id" => 1, "name" => "SAINT MARTIN", "code" => "MAF", "code2" => "ST" ],
            ["provider_id" => 1, "name" => "SAINT VINCENT AND THE GRENADINES", "code" => "VCT", "code2" => "SA" ],
            ["provider_id" => 1, "name" => "SAMOA", "code" => "WSM", "code2" => "SN" ],
            ["provider_id" => 1, "name" => "SAN MARINO", "code" => "SMR", "code2" => "RS" ],
            ["provider_id" => 1, "name" => "SAO TOME AND PRÍNCIPE", "code" => "STP", "code2" => "SC" ],
            ["provider_id" => 1, "name" => "SAUDI ARABIA", "code" => "SAU", "code2" => "SL" ],
            ["provider_id" => 1, "name" => "SENEGAL", "code" => "SEN", "code2" => "SG" ],
            ["provider_id" => 1, "name" => "SERBIA", "code" => "SRB", "code2" => "SX" ],
            ["provider_id" => 1, "name" => "SEYCHELLES", "code" => "SYC", "code2" => "SK" ],
            ["provider_id" => 1, "name" => "SIERRA LEONE", "code" => "SLE", "code2" => "SI" ],
            ["provider_id" => 1, "name" => "SINGAPORE", "code" => "SGP", "code2" => "SB" ],
            ["provider_id" => 1, "name" => "SINT MAARTEN (DUTCH PART)", "code" => "SXM", "code2" => "SO" ],
            ["provider_id" => 1, "name" => "SLOVAKIA", "code" => "SVK", "code2" => "ZA" ],
            ["provider_id" => 1, "name" => "SLOVENIA", "code" => "SVN", "code2" => "GS" ],
            ["provider_id" => 1, "name" => "SOLOMON ISLANDS", "code" => "SLB", "code2" => "SS" ],
            ["provider_id" => 1, "name" => "SOMALIA", "code" => "SOM", "code2" => "ES" ],
            ["provider_id" => 1, "name" => "SOUTH AFRICA", "code" => "ZAF", "code2" => "LK" ],
            ["provider_id" => 1, "name" => "SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS", "code" => "SGS", "code2" => "SH" ],
            ["provider_id" => 1, "name" => "SOUTH SUDAN", "code" => "SSD", "code2" => "PM" ],
            ["provider_id" => 1, "name" => "SPAIN", "code" => "ESP", "code2" => "SD" ],
            ["provider_id" => 1, "name" => "SRI LANKA", "code" => "LKA", "code2" => "SS" ],
            ["provider_id" => 1, "name" => "ST. PIERRE AND MIQUELON", "code" => "SPM", "code2" => "SX" ],
            ["provider_id" => 1, "name" => "SUDAN", "code" => "SDN", "code2" => "SR" ],
            ["provider_id" => 1, "name" => "SURINAME", "code" => "SUR", "code2" => "SJ" ],
            ["provider_id" => 1, "name" => "SVALBARD AND JAN MAYEN ISLANDS", "code" => "SJM", "code2" => "SZ" ],
            ["provider_id" => 1, "name" => "SWAZILAND", "code" => "SWZ", "code2" => "SE" ],
            ["provider_id" => 1, "name" => "SWEDEN", "code" => "SWE", "code2" => "CH" ],
            ["provider_id" => 1, "name" => "SWITZERLAND", "code" => "CHE", "code2" => "SY" ],
            ["provider_id" => 1, "name" => "SYRIAN ARAB REPUBLIC", "code" => "SYR", "code2" => "TW" ],
            ["provider_id" => 1, "name" => "TAIWAN", "code" => "TWN", "code2" => "TJ" ],
            ["provider_id" => 1, "name" => "TAJIKISTAN", "code" => "TJK", "code2" => "TZ" ],
            ["provider_id" => 1, "name" => "TANZANIA, UNITED REPUBLIC OF", "code" => "TZA", "code2" => "TH" ],
            ["provider_id" => 1, "name" => "THAILAND", "code" => "THA", "code2" => "TL" ],
            ["provider_id" => 1, "name" => "TIMOR-LESTE", "code" => "TLS", "code2" => "TG" ],
            ["provider_id" => 1, "name" => "TOGO", "code" => "TGO", "code2" => "TK" ],
            ["provider_id" => 1, "name" => "TOKELAU", "code" => "TKL", "code2" => "TO" ],
            ["provider_id" => 1, "name" => "TONGA", "code" => "TON", "code2" => "TT" ],
            ["provider_id" => 1, "name" => "TRINIDAD AND TOBAGO", "code" => "TTO", "code2" => "TN" ],
            ["provider_id" => 1, "name" => "TUNISIA", "code" => "TUN", "code2" => "TR" ],
            ["provider_id" => 1, "name" => "TURKEY", "code" => "TUR", "code2" => "TM" ],
            ["provider_id" => 1, "name" => "TURKMENISTAN", "code" => "TKM", "code2" => "TC" ],
            ["provider_id" => 1, "name" => "TURKS AND CAICOS ISLANDS", "code" => "TCA", "code2" => "TV" ],
            ["provider_id" => 1, "name" => "TUVALU", "code" => "TUV", "code2" => "UG" ],
            ["provider_id" => 1, "name" => "UGANDA", "code" => "UGA", "code2" => "UA" ],
            ["provider_id" => 1, "name" => "UKRAINE", "code" => "UKR", "code2" => "AE" ],
            ["provider_id" => 1, "name" => "UNITED ARAB EMIRATES", "code" => "ARE", "code2" => "GB" ],
            ["provider_id" => 1, "name" => "UNITED KINGDOM", "code" => "GBR", "code2" => "UM" ],
            ["provider_id" => 1, "name" => "UNITED STATES MINOR OUTLYING ISLANDS", "code" => "UMI", "code2" => "US" ],
            ["provider_id" => 1, "name" => "URUGUAY", "code" => "URY", "code2" => "UY" ],
            ["provider_id" => 1, "name" => "USA", "code" => "USA", "code2" => "UZ" ],
            ["provider_id" => 1, "name" => "UZBEKISTAN", "code" => "UZB", "code2" => "VU" ],
            ["provider_id" => 1, "name" => "VANUATU", "code" => "VUT", "code2" => "VA" ],
            ["provider_id" => 1, "name" => "VENEZUELA", "code" => "VEN", "code2" => "VE" ],
            ["provider_id" => 1, "name" => "VIETNAM", "code" => "VNM", "code2" => "VN" ],
            ["provider_id" => 1, "name" => "VIRGIN ISLANDS (BRITISH)", "code" => "VGB", "code2" => "VG" ],
            ["provider_id" => 1, "name" => "VIRGIN ISLANDS (U.S.)", "code" => "VIR", "code2" => "VI" ],
            ["provider_id" => 1, "name" => "WALLIS AND FUTUNA ISLANDS", "code" => "WLF", "code2" => "WF" ],
            ["provider_id" => 1, "name" => "WESTERN SAHARA", "code" => "ESH", "code2" => "EH" ],
            ["provider_id" => 1, "name" => "YEMEN", "code" => "YEM", "code2" => "YE" ],
            ["provider_id" => 1, "name" => "YUGOSLAVIA", "code" => "YUG", "code2" => "CD" ],
            ["provider_id" => 1, "name" => "ZAMBIA", "code" => "ZMB", "code2" => "ZM" ],
            ["provider_id" => 1, "name" => "ZIMBABWE", "code" => "ZWE", "code2" => "ZW" ],
        ];

        foreach($countries as $country){
            DB::table('countries')->insert($country);
        }
    }
}
