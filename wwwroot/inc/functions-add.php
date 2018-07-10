<?php

# Ce fichier comporte les fonctions ajoutées au dossier RackTables 

/////////////////////////////////////////////////////////////////////////////////////////
/*     Fonction permettant l'affichage des ports restant des PatchPanel pour une baie  */
function testPatchPanel($dataRack)
{
	echo "<table border=0 cellspacing=0 cellpadding=3 width='100%'>\n";
	echo "<table border=0 cellspacing=0 cellpadding=3 width='100%'>\n";
	echo '<tr>'.
			'<th width='.'50%'.'class=tdleft>'.
				'<strong>PatchPanel de la baie :</strong>'.
			'</th>'.
			'<td class=tdleft><strong>PatchPanel distant :</strong></td>'.
			'<td class=tdleft><strong>Ports utilisés :</strong></td></tr>';	
	foreach ($dataRack['mountedObjects'] as $id)
	{
		$req=usePreparedSelectBlade
 		(
			'SELECT * '. 
			'FROM Object '.
			'WHERE id='.$id.''
		);
		$res = $req->fetchAll();
		if ($res[0]['objtype_id'] == 9)
		{
		$port_tot = listPortObj($id);
		$port_used = listPortObj_used($id);
        $PatchPdist = distPatchPanel($id,0);
        $IdPatchPdist = distPatchPanel($id,1);
		echo '<tr>'.
			'<th width='.'50%'.'class=tdleft>'.
				'<a href =index.php?page=object&object_id='.$id.'>'.$res[0]['name'].'</a>'.
			'</th>'.
			'<td class=tdleft>'.	
				'<strong><a href =index.php?page=object&object_id='.$IdPatchPdist.'>'.$PatchPdist.'</a></strong>'.
			'</td>'.
			'<td class=tdleft>'.$port_used.' / '.$port_tot.'</td></tr>';	
		//echo '<p><strong><a href ="index.php?page=object&object_id='.$id.'">'.$res[0]['name'].'</a></strong></p>';
	//	print_r($neyme);
		}
	}
	echo "</table>\n";
}
function listPortObj_used ($obj_id)
{
	$req=usePreparedSelectBlade
 		(
			'SELECT porta,portb,id '. 
			'FROM Link l '.
			'JOIN Port p ON l.porta=p.id OR l.portb=p.id '.
			'WHERE p.object_id='.$obj_id.';'
		);
		
	$res = $req->fetchAll();
	$portsUsed=count($res);
	return $portsUsed ;
}
function listPortObj ($obj_id)
{
	$req=usePreparedSelectBlade
 	(
		'SELECT porta,portb,id '. 
		'FROM LinkBackend lbe '.
		'JOIN Port p ON lbe.porta=p.id OR lbe.portb=p.id '.
		'WHERE p.object_id='.$obj_id.';'
	);
	
	$res = $req->fetchAll();
	$nbrport=count($res);
	return $nbrport ;
}	

//////////////////////////////////////////////////////////////////////
/*     Nouvelle Fonction permettant de déterminer le nom du 	    */
/*     PatchPanel distant 				 	    */
function distPatchPanel ($obj_id,$obj_id_dist)
{
	$req=usePreparedSelectBlade
	(
		'SELECT porta,portb,id '. 
		'FROM LinkBackend lbe '.
		'JOIN Port p ON lbe.porta=p.id OR lbe.portb=p.id '.
		'WHERE p.object_id='.$obj_id.';'
	);
	
	$res = $req->fetchAll();
	
	if($res[0]['porta'] == $res[0]['id']){
		if (isset($res[0]['portb'])){
            
			$req=usePreparedSelectBlade
			(
			   'SELECT o.name, o.id '. 
			   'FROM Object o '.
			   'JOIN Port p ON o.id=p.object_id '.
			   'WHERE p.id='.$res[0]['portb'].';'
		   	);
		   
            $res2 = $req->fetchAll();
            if($obj_id_dist == 1)
                return $res2[0]['id'];
		    return $res2[0]['name'];
		}
	}else if ($res[0]['portb'] == $res[0]['id']){
		if (isset ($res[0]['porta'])){
            if($obj_id_dist == 1)
            {
                return $res[0]['porta'];
            }
			$req=usePreparedSelectBlade
			(
			   'SELECT o.name, o.id '. 
			   'FROM Object o '.
			   'JOIN Port p ON o.id=p.object_id '.
			   'WHERE p.id='.$res[0]['porta'].';'
			);
			$res2 = $req->fetchAll();
            if($obj_id_dist == 1)
                return $res2[0]['id'];
            return $res2[0]['name'];
		}
	}
}
//////////////////////////////////////////////////////////////////////
/*     Nouvelle Fonction permettant la coloration des U réservés    */
function reservedColor ($id_obj)
{
	if (isset($id_obj))
	{
		$req=usePreparedSelectBlade
 		(
			'SELECT tag_id '. 
			'FROM TagStorage '.
			'WHERE entity_id='.$id_obj.' AND tag_id=6'//id du tag réservé est le 6
		);	
		$res = $req->fetchAll();
		return	$res[0]['tag_id'];
	}
	return 0;

}
////////////////////////////////////////////////////////////////////////
function getPDUforRack($DataRack)
{
	$qTot=usePreparedSelectBlade
 	(
		'SELECT DISTINCT av.uint_value,av.object_id '. 
		'FROM AttributeValue av '.
		'JOIN RackObject ro ON av.object_id=ro.id '.
		'JOIN RackSpace rs ON ro.id=rs.object_id '.
		'WHERE rs.rack_id='.$DataRack[id].' AND av.object_tid=2 AND attr_id=10009'
	);

	$Tot = $qTot->fetchAll();
	
	foreach ($Tot as $ligne)
		$resPtot += $ligne['uint_value'];

	$qUti=usePreparedSelectBlade 
	(	
		'SELECT DISTINCT av.uint_value,av.object_id '.
		'FROM AttributeValue av '.
		'JOIN RackObject ro ON av.object_id=ro.id '.
		'JOIN RackSpace rs ON ro.id=rs.object_id '.
		'WHERE rs.rack_id='.$DataRack[id].' AND av.object_tid=2 AND av.attr_id=10010'
	);

	$Uti = $qUti->fetchAll();
	foreach ($Uti as $ligne)
		$resUti += $ligne['uint_value'];
	return $resUti/$resPtot;
}

function getUreservedforRow($id_row)
{
	$query = usePreparedSelectBlade
	(
		'SELECT rs.object_id '.
		'FROM RackSpace rs '.
		'JOIN TagStorage ts ON rs.object_id=ts.entity_id '.
		'JOIN Rack r ON rs.rack_id=r.id '.
		'WHERE ts.tag_id=6 AND rs.atom="interior" AND r.row_id='.$id_row.';'
	);
	$array=$query->fetchALL();
	$reserved=count($array);
	return $reserved;
}

function getUbusyforRow($list_rack, $id_row)
{
	foreach ($list_rack as $key => $value)
	{
		$query = usePreparedSelectBlade
		(
			'SELECT DISTINCT unit_no '.
			'FROM RackSpace rs '.
			'JOIN Rack r ON rs.rack_id=r.id '.
			'WHERE rack_id='.$key.' AND r.row_id='.$id_row.' AND atom="interior";'
		);
		$array=$query->fetchALL();
		$counter += count($array);
	}
	return $counter;
}
?>