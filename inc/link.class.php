<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2009 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Julien Dombre
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

// CLASSES link
class Link extends CommonDBTM {

   // From CommonDBTM
   public $table = 'glpi_links';
   public $type = 'Link';

   static function getTypeName() {
      global $LANG;

      return $LANG['setup'][87];
   }

   function canCreate() {
      return haveRight('link', 'w');
   }

   function canView() {
      return haveRight('link', 'r');
   }

   function defineTabs($ID,$withtemplate) {
      global $LANG;

      $ong=array();
      $ong[1]=$LANG['title'][26];
      return $ong;
   }

   function cleanDBonPurge($ID) {
      global $DB;

      $query2="DELETE
               FROM `glpi_links_itemtypes`
               WHERE `links_id`='$ID'";
      $DB->query($query2);
   }

   /**
    * Print the link form
    *
    * Print g��al link form
    *
    *@param $target filename : where to go when done.
    *@param $ID Integer : Id of the link to print
    *
    *@return Nothing (display)
    *
    **/
   function showForm ($target,$ID) {
      global $CFG_GLPI, $LANG;

      if (!haveRight("link","r")) {
         return false;
      }
      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w');
      }

      $this->showTabs($ID);
      $this->showFormHeader($target,$ID,'',2);

      echo "<tr class='tab_bg_1'><td height='23'>".$LANG['links'][6]."&nbsp;:</td>";
      echo "<td colspan='3'>[LOGIN], [ID], [NAME], [LOCATION], [LOCATIONID], [IP], [MAC], [NETWORK],
                            [DOMAIN], [SERIAL], [OTHERSERIAL], [USER], [GROUP]</td></tr>";

      echo "<tr class='tab_bg_1'><td>".$LANG['common'][16]."&nbsp;:</td>";
      echo "<td colspan='3'>";
      autocompletionTextField("name",$this->table,"name",$this->fields["name"],84);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".$LANG['links'][1]."&nbsp;:</td>";
      echo "<td colspan='2'>";
      autocompletionTextField("link",$this->table,"link",$this->fields["link"],84);
      echo "</td><td width='1'></td></tr>";

      echo "<tr class='tab_bg_1'><td>".$LANG['links'][9]."&nbsp;:</td>";
      echo "<td colspan='3'>";
      echo "<textarea name='data' rows='10' cols='96'>".$this->fields["data"]."</textarea>";
      echo "</td></tr>";

      $this->showFormButtons($ID,'',2);
      echo "<div id='tabcontent'></div>";
      echo "<script type='text/javascript'>loadDefaultTab();</script>";

      return true;
   }

   function getSearchOptions() {
      global $LANG;

      $tab = array();
      $tab['common'] = $LANG['common'][32];

      $tab[1]['table']         = 'glpi_links';
      $tab[1]['field']         = 'name';
      $tab[1]['linkfield']     = 'name';
      $tab[1]['name']          = $LANG['common'][16];
      $tab[1]['datatype']      = 'itemlink';
      $tab[1]['itemlink_type'] = 'Link';

      $tab[2]['table']     = 'glpi_links';
      $tab[2]['field']     = 'id';
      $tab[2]['linkfield'] = '';
      $tab[2]['name']      = $LANG['common'][2];

      $tab[3]['table']     = 'glpi_links';
      $tab[3]['field']     = 'link';
      $tab[3]['linkfield'] = 'link';
      $tab[3]['name']      = $LANG['links'][1];

      $tab[80]['table']     = 'glpi_entities';
      $tab[80]['field']     = 'completename';
      $tab[80]['linkfield'] = 'entities_id';
      $tab[80]['name']      = $LANG['entity'][0];

      return $tab;
   }

   /**
    * Generate link
    *
    * @param $link string : original string content
    * @param $item CommonDBTM : item used to make replacements
    * @return array of link contents (may have several when item have several IP / MAC cases)
    */
   static function generateLinkContents($link, CommonDBTM $item) {
      global $DB;

      if (strstr($link,"[ID]")) {
         $link=str_replace("[ID]",$item->fields['id'],$link);
      }
      if (strstr($link,"[LOGIN]") && isset($_SESSION["glpiname"])) {
         if (isset($_SESSION["glpiname"])) {
            $link=str_replace("[LOGIN]",$_SESSION["glpiname"],$link);
         }
      }

      if (strstr($link,"[NAME]")) {
         $link=str_replace("[NAME]",$item->getName(),$link);
      }
      if (strstr($link,"[SERIAL]")) {
         if ($tmp=$item->getField('serial')) {
            $link=str_replace("[SERIAL]",$tmp,$link);
         }
      }
      if (strstr($link,"[OTHERSERIAL]")) {
         if ($tmp=$item->getField('otherserial')) {
            $link=str_replace("[OTHERSERIAL]",$tmp,$link);
         }
      }
      if (strstr($link,"[LOCATIONID]")) {
         if ($tmp=$item->getField('locations_id')) {
            $link=str_replace("[LOCATIONID]",$tmp,$link);
         }
      }
      if (strstr($link,"[LOCATION]")) {
         if ($tmp=$item->getField('locations_id')) {
            $link=str_replace("[LOCATION]",Dropdown::getDropdownName("glpi_locations",$tmp),$link);
         }
      }
      if (strstr($link,"[NETWORK]")) {
         if ($tmp=$item->getField('networks_id')) {
            $link=str_replace("[NETWORK]",Dropdown::getDropdownName("glpi_networks",$tmp),$link);
         }
      }
      if (strstr($link,"[DOMAIN]")) {
         if ($tmp=$item->getField('domains_id')) {
            $link=str_replace("[DOMAIN]",Dropdown::getDropdownName("glpi_domains",$tmp),$link);
         }
      }
      if (strstr($link,"[USER]")) {
         if ($tmp=$item->getField('users_id')) {
            $link=str_replace("[USER]",Dropdown::getDropdownName("glpi_users",$tmp),$link);
         }
      }
      if (strstr($link,"[GROUP]")) {
         if ($tmp=$item->getField('groups_id')) {
            $link=str_replace("[GROUP]",Dropdown::getDropdownName("glpi_groups",$tmp),$link);
         }
      }
      $ipmac=array();
      $i=0;

      if (!strstr($link,"[IP]") && !strstr($link,"[MAC]")) {
         return array($link);
      } else { // Return sevral links id several IP / MAC

         $links=array();
         $query2 = "SELECT `ip`, `mac`, `logical_number`
                  FROM `glpi_networkports`
                  WHERE `items_id` = '".$item->fields['id']."'
                        AND `itemtype` = '".get_class($item)."'
                  ORDER BY `logical_number`";
         $result2=$DB->query($query2);
         if ($DB->numrows($result2)>0) {
            while ($data2=$DB->fetch_array($result2)) {
               $ipmac[$i]['ip']=$data2["ip"];
               $ipmac[$i]['mac']=$data2["mac"];
               $ipmac[$i]['number']=$data2["logical_number"];
               $i++;
            }
         }

         // Add IP/MAC internal switch
         if (get_class($item) == 'NetworkEquipment') {
            $tmplink=$link;
            $tmplink=str_replace("[IP]",$item->getField('ip'),$tmplink);
            $tmplink=str_replace("[MAC]",$item->getField('mac'),$tmplink);

            $links["$name - $tmplink"]=$tmplink;
         }
         if (count($ipmac)>0) {
            foreach ($ipmac as $key => $val) {
               $tmplink=$link;
               $disp=1;
               if (strstr($link,"[IP]")) {
                  if (empty($val['ip'])) {
                     $disp=0;
                  } else {
                     $tmplink=str_replace("[IP]",$val['ip'],$tmplink);
                  }
               }
               if (strstr($link,"[MAC]")) {
                  if (empty($val['mac'])) {
                     $disp=0;
                  } else {
                     $tmplink=str_replace("[MAC]",$val['mac'],$tmplink);
                  }
               }
               if ($disp) {
                  $links["$name #" .$val['number']." - $tmplink"]=$tmplink;
               }
            }
         }

         if (count($links)) {
            return $links;
         } else {
            return array($link);
         }
      }

   }

   /**
    * Show Links for an item
    *
    * @param $itemtype integer : item type
    * @param $ID integer : item ID
    */
   static function showForItem($itemtype,$ID) {
      global $DB,$LANG,$CFG_GLPI;

      if (!class_exists($itemtype)) {
         return false;
      }

      $item = new $itemtype;
      if (!$item->getFromDB($ID)) {
         return false;
      }

      if (!haveRight("link","r")) {
         return false;
      }

      $query="SELECT `glpi_links`.`id`, `glpi_links`.`link` AS link, `glpi_links`.`name` AS name ,
                     `glpi_links`.`data` AS data
              FROM `glpi_links`
              INNER JOIN `glpi_links_itemtypes` ON `glpi_links`.`id`=`glpi_links_itemtypes`.`links_id`
              WHERE `glpi_links_itemtypes`.`itemtype`='$itemtype' " .
                    getEntitiesRestrictRequest(" AND","glpi_links","entities_id",
                                               $item->getEntityID(),true)."
              ORDER BY name";

      $result=$DB->query($query);

      if ($DB->numrows($result)>0) {
         echo "<div class='center'><table class='tab_cadre'><tr><th>".$LANG['title'][33]."</th></tr>";
         while ($data=$DB->fetch_assoc($result)) {
            $name=$data["name"];
            if (empty($name)) {
               $name=$data["link"];
            }
            $file=trim($data["data"]);


//                 else {
//                   echo "<tr class='tab_bg_2'><td><a target='_blank' href='$link'>$name</a></td></tr>";
//                }

            $tosend=false;
            if (empty($file)) {
               $link=$data["link"];
            } else {
               $link=$data['name'];
               $tosend=true;
            }

            $contents=Link::generateLinkContents($link,$item);
            if (count($contents)) {
               foreach ($contents as $title => $link) {
                  $current_name=$name;
                  if (!empty($title)){
                     $current_name=$title;
                  }
                  $clean_name=Link::generateLinkContents($current_name,$item);

                  $url=$link;
                  if ($tosend) {
                     $url=$CFG_GLPI["root_doc"]."/front/link.send.php?lID=".
                           $data['id']."&amp;itemtype=$itemtype&amp;id=$ID";
                  }
               echo "<tr class='tab_bg_2'>";
               echo "<td><a href='$url' target='_blank'>".
                           $clean_name[0]."</a></td></tr>";

               }
            }
         }
         echo "</table></div>";
      } else {
         echo "<div class='center'><strong>".$LANG['links'][7]."</strong></div>";
      }
   }
}

?>
