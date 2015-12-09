<div class="content_xls" style="border: solid 1px #999999;">
    <div id="grid">
    	<table width="100%" border="0" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th><?php echo "ID";?></th>
                    <th><?php echo "Provinsi";?></th>
                    <th><?php echo "Suite";?></th>
                    <th><?php echo "Star 5";?></th>
                    <th><?php echo "Star 4";?></th>
                    <th><?php echo "Star 3";?></th>
                    <th><?php echo "Star 2";?></th>
                    <th><?php echo "Star 1";?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                for($row=2; $row<=$highestRow; ++$row):
                ?>
                <tr>
                    <td><?php echo $objWorksheet->getCell($posisi['id'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['provinsi'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['suite'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['star5'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['star4'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['star3'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['star2'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['star1'].$row)->getValue(); ?></td>
                </tr>
                <?php
                endfor;
                ?>                
            </tbody>
            <p><?php echo $links; ?></p>
        </table>
    </div>            
</div>