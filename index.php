<?php

$logs = fopen("C:\\Apache24\\htdocs\\hub\\bars\\logs.txt", "a");

$conn_str = '(DESCRIPTION=(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 192.168.101.251)(PORT = 1521)))(CONNECT_DATA=(SID=MSCH123)))';
		
$conn = oci_connect('dev', 'def', $conn_str, 'AL32UTF8');

$sql_ending =<<<'sql'
commit;
end;
sql;

$sql_delete =<<<'sql'
begin

delete 
 	from dev.d_nommodif_packing
  	where cid = '165383667';
delete 
 	from  dev.d_packing  where EXISTS
 	(select * from dev.d_packing t join dev.d_measures t1 on  t.PACK_MEASURE=t1.id  
  	where cid = '165473927');

delete
    from dev.d_nommodif
    where cid = '165383667';
delete
    from dev.d_nombase
    where cid = '165383667';
delete
    from dev.d_measures
    where cid = '165473927';    
commit;
end;
sql;

$stmt = oci_parse($conn, $sql_delete);

oci_execute($stmt);

oci_free_statement($stmt);

$json_nom = $_POST['nom'];

$nom = json_decode($json_nom, true);

$row_key = '';

foreach ($nom as $row){

	if($row_key != $row['nom_name'] . $row['mnn'] . $row['med_forms'] and empty($row_key) === false){

		$nommodif[] = $row;
		
	}else{

		$sql_insert = <<<'sql'
		declare
			nom_id number (17, 0);
			msr_id number (17, 0);
			mod_id number (17, 0);
		    function get_measure_id(code in number, mnemo in varchar2, name in varchar2)
	     	return number
	        is
	        msr_id number(17, 0);
	       	begin
	            select
	                id
	            into
	                msr_id
	            from
	                d_measures
	            where
	                cid = '165473927'
	                and code = code
	                and rownum = 1;
	            return msr_id;
        	exception
	            when NO_DATA_FOUND then
	                insert
	                    into dev.d_measures (id, code, mnemocode, m_name, cid, version)
	                        values (D_GEN_ID, code, mnemo, name, '165473927', '4856326')
	                    returning id into msr_id;
	                commit;
	            return msr_id;    
        	end;

        	  procedure insert_nommodif_packing(mod_id in number, code in varchar2, name in varchar2, piece1 in number) is


    			pack_code    VARCHAR(100);
   				pack_name    VARCHAR(100);
    			piece        number(17,0);

			begin
   			select 
   				pack_code,
   				pack_name,
   				piece
   				into 
   				pack_code,
   				pack_name,
   				piece
   				from d_packing 
  				where pack_code = code
   				and rownum = 1; 
                    insert
             			into dev.d_nommodif_packing (id, version, pid, pack_code, pack_name, measure, pack_measure, cid, is_main, pack_count)
               				values (D_GEN_ID, '6565853', mod_id,pack_code,pack_name,msr_id,msr_id,'165383667',1,piece1 );
               				EXCEPTION
              					 when no_data_found then 
                    insert  
             			into dev.d_packing (id, pack_code, pack_name, pack_measure, piece, version)
              				values(D_GEN_ID,code, name, msr_id, piece1,'6571395');
                    insert
             			into dev.d_nommodif_packing (id, version, pid, pack_code, pack_name, measure, pack_measure, cid, is_main, pack_count)
               				values (D_GEN_ID, '6565853', mod_id,code, name, msr_id,msr_id,'165383667', 1, piece1 );
               
   			 end;

			begin
			msr_id := get_measure_id(&main_measure);	
					insert
						into dev.d_nombase (id, version, cid, nom_type, nom_code, nom_name, nom_group, main_measure, mnn, med_forms)
							values (D_GEN_ID, '6561979', '165383667', '2', &nom_code, &nom_name, &nom_group, msr_id, &mnn, &med_forms)
								returning id into nom_id;
		sql;

		foreach ($nommodif[0] as $key => $value) {
			str_replace('&' . $key, strpos($value, 'select') === false ? $value : '\'' . $value . '\'', $sql_insert);
		}

		foreach ($nommodif as $row) {

			$sql_shard = <<<'sql'
			insert
				into dev.d_nommodif (id, version, pid, mod_code, cid, mod_name, main_measure)
					values (D_GEN_ID, '6562015', nom_id, &mod_code, '165383667', &mod_name, msr_id);
					returning id into mod_id;

			insert_nommodif_packing(mod_id, &packing);
			sql;		

			foreach ($row as $key => $value) {
				str_replace('&' . $key, strpos($value, 'select') === false ? $value : '\'' . $value . '\'', $sql_shard);
			}

			$sql_insert = $sql_insert . $sql_shard;

		}

		$sql_insert = $sql_insert . $sql_ending;

		$stmt = oci_parse($conn, $sql_insert);

		oci_execute($stmt);

		oci_free_statement($stmt);
		
	}

	$row_key = $row['nom_name'] . $row['mnn'] . $row['med_forms'];

}

oci_close($conn);

fclose($logs);	

?>