<div class="content_xls" style="border: solid 1px #999999;">
    <div id ="grid">
    	<table width="100%" border="0" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th><?php echo "ID";?></th>
                    <th><?php echo "Departure City";?></th>
                    <th><?php echo "Destination City";?></th>
                    <th><?php echo "Business Class";?></th>
                    <th><?php echo "Economy Class";?></th>                    
                </tr>
            </thead>
            <tbody>
                <?php
                for($row=2; $row<=$highestRow; ++$row):
                ?>
                <tr>
                    <td><?php echo $objWorksheet->getCell($posisi['id'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['asal'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['tujuan'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['bisnis'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['ekonomi'].$row)->getValue(); ?></td>                   
                </tr>
                <?php
                endfor;				
                ?>                
            </tbody>   
            <p><?php echo $links; ?></p>        
        </table>
    </div>            
</div>