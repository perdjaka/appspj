<div class="content_xls" style="border: solid 1px #999999;">
    <div id="grid">
       <table width="100%" border="0" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th><?php echo "ID"; ?></th>
                    <th><?php echo "Provinsi";?></th>
                    <th><?php echo "Fullboard Luar II";?></th>
                    <th><?php echo "Fullboard Luar III";?></th>
                    <th><?php echo "Fullboard Luar IV";?></th>
                    <th><?php echo "Fullboard Dalam II";?></th>
                    <th><?php echo "Fullboard Dalam III";?></th>
                    <th><?php echo "Fullboard Dalam IV";?></th>
                    <th><?php echo "Fullday Dalam II";?></th>
                    <th><?php echo "Fullday Dalam III";?></th>
                    <th><?php echo "Fullday Dalam IV";?></th>
                    <th><?php echo "Uang Saku Murni ABCD";?></th>
                    <th><?php echo "Uang Saku Murni E";?></th>
                    <th><?php echo "Uang Saku Murni F";?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                for($row=2; $row<=$highestRow; ++$row):
                ?>
                <tr>
                    <td><?php echo $objWorksheet->getCell($posisi['id'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['provinsi'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['fullboard_luar_II'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['fullboard_luar_III'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['fullboard_luar_IV'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['fullboard_dalam_II'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['fullboard_dalam_III'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['fullboard_dalam_IV'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['fullday_dalam_II'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['fullday_dalam_III'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['fullday_dalam_IV'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['uang_saku_murni_ABCD'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['uang_saku_murni_E'].$row)->getValue(); ?></td>
                    <td><?php echo $objWorksheet->getCell($posisi['uang_saku_murni_F'].$row)->getValue(); ?></td>                    
                </tr>
                <?php
                endfor;
                ?>
            </tbody>
            <p><?php echo $links; ?></p>
        </table>
    </div>            
</div>