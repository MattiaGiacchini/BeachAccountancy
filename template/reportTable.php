<div class="row" id="mainRow">
    <div class="col-12">
        <form class="hidden-print row g-4 card-block bg-faded" action="report.php" method="get" id="boundForm">
            <div class="col-auto">
                <div class="form-floating">
                    <input type="date" class="form-control" name="lowerBound" id="reportLowerBound" value="">
                    <label for="reportLowerBound">Lower Bound</label>
                </div>
            </div>
            <div class="col-auto">
                <div class="form-floating">
                    <input type="date" class="form-control" name="upperBound" id="reportUpperBound" value="">
                    <label for="reportUpperBound">Upper Bound</label>
                </div>
            </div>
            <div class="col-auto">
                <div class="form-control-lg">
                    <input class="btn-check" type="checkbox" id="reportA" name="reportA" tabindex="-1" />
                    <label class="btn btn-outline-secondary" for="reportA">A</label>
                </div>
            </div>
            <div class="col-auto">
                <div class="form-control-lg">
                    <button type="submit" class="btn btn-primary mb-3">Filter</button>
                </div>
            </div>
        </form>
    </div>
    <div class="col-12">

        <table id="reportTable" class="table align-middle text-center table-hover" data-show-print="true">
            <thead>
                <tr class="align-middle">
                    <th scope="col">#</th>
                    <th scope="col">Nome</th>
                    <th scope="col">Omb</th>
                    <th scope="col">Lett</th>
                    <th scope="col">Check-in</th>
                    <th scope="col">Check-out</th>
                    <th scope="col">GG</th>
                    <th scope="col">Prezzo</th>
                    <th scope="col" colspan="2">Variazione</th>
                    <th scope="col">Valore €</th>
                    <th scope="col">Totale €</th>
                    <th scope="col" class="hidden-print">Azione</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($templateParams["allBS"] as $bs): ?>
                    <tr id="<?php echo $bs["idBS"] ?>">
                        <th scope="row" rowspan="<?php echo $bs["numLines"] ?>"><?php echo $bs["numBS"] ?></th>
                        <td rowspan="<?php echo $bs["numLines"] ?>"><?php echo $bs["name"] ?></td>
                        <td rowspan="<?php echo $bs["numLines"] ?>"><?php echo $bs["umbrellas"] ?></td>
                        <td rowspan="<?php echo $bs["numLines"] ?>"><?php echo $bs["beds"] ?></td>
                        <td rowspan="<?php echo $bs["numLines"] ?>"><?php echo date("d/m", strtotime($bs["checkin"])) ?></td>
                        <td rowspan="<?php echo $bs["numLines"] ?>"><?php echo date("d/m", strtotime($bs["checkout"])) ?></td>

                        <?php if ($bs["name"] == "ANNULLATO"): ?>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td rowspan="<?php echo "1" ?>" class="hidden-print">
                                    <div class="btn-group" role="group">
                                        <button value="<?php echo $bs["idBS"] ?>" type="button" class="btn btn-warning editBSbutton">Edit</button>
                                        <button value="<?php echo $bs["idBS"] ?>" type="button" class="btn btn-danger deleteBSbutton">Del</button>
                                    </div>
                                </td>
                        <?php endif; ?>

                        <?php $counter = 0 ?>
                            <?php foreach ($templateParams["BSlines"][$bs["idBS"]] as $line): ?>
                                <?php if ($counter > 0): ?>
                                    <?php echo "<tr>" ?>
                                <?php endif; ?>
                                <td><?php echo $line["days"] ?></td>
                                <td><?php echo $line["price"] ?></td>
                                <td><?php echo $bs["varBeds"] == 0 ? "" : $bs["varBeds"] ?></td>
                                <td><?php echo "x " . $bs["varUmbrellas"] ?></td>
                                <td><?php echo number_format((float)$line["lineValue"], 2, '.', ',') ?></td>
                                <?php if ($counter > 0): ?>
                                    <?php echo "</tr>" ?>
                                <?php endif; ?>
                                <?php if ($counter == 0): ?>
                                    <td id="totalBS" rowspan="<?php echo $bs["numLines"] ?>"><?php echo number_format((float)$bs["totBS"], 2, '.', ',') ?></td>
                                    <td rowspan="<?php echo $bs["numLines"] ?>" class="hidden-print">
                                        <div class="btn-group" role="group">
                                            <button value="<?php echo $bs["idBS"] ?>" type="button" class="btn btn-warning editBSbutton">Edit</button>
                                            <button value="<?php echo $bs["idBS"] ?>" type="button" class="btn btn-danger deleteBSbutton">Del</button>
                                        </div>
                                    </td>
                                <?php endif; ?>
                                <?php $counter++ ?>

                            <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                <?php $iva = 22 ?>
                <?php $total = $templateParams["totalPrice"] ?>
                <?php $totalNoIVA = $total / (1 + $iva / 100) ?>
                <tr class="table-info fw-bold fs-2">
                    <td colspan="8" class="text-end">Totale complessivo</td>
                    <td colspan="4"><?php echo "€ " . number_format((float)$templateParams["totalPrice"], 2, ',', '.')?></td>
                </tr>
                <tr class="table-info fw-bold fs-2">
                    <td colspan="8" class="text-end">IVA</td>
                    <td colspan="4"><?php echo "€ " . number_format((float) $total - $totalNoIVA, 2, ',', '.') ?></td>
                </tr>
                <tr class="table-info fw-bold fs-2">
                    <td colspan="8" class="text-end">Imponibile</td>
                    <td colspan="4"><?php echo "€ " . number_format((float) $totalNoIVA, 2, ',', '.')?></td>
                </tr>
            </tbody>
        </table>

    </div>

</div>
