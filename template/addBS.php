<div class="row" id="mainRow">
    <div class="col-8">

        <form class="row g-3 card-block bg-faded" action="#" id="formBS" method="post">

            <div class="col-4">
                <div class="form-floating">
                    <input class="form-control" type="number" id="numBS" name="numBS" required  min="0" step="1" value="<?php echo $templateParams["nextBSnumber"]; ?>" placeholder="Numero BS"/>
                    <label for="numBS">Numero BS</label>
                </div>
            </div>
            <div class="col-2">
                <div class="form-control-lg">
                    <input class="btn-check btn-lg" type="checkbox" id="bsA" name="friendly" tabindex="-1" />
                    <label class="btn btn-outline-secondary" for="bsA">A</label>
                </div>
            </div>

            <div class="col-8">
                <div class="form-floating">
                    <input class="form-control" type="text" id="clientName" name="name" value="" autofocus required placeholder="Nome Ospite">
                    <label for="clientName">Nome Ospite</label>
                </div>
            </div>

            <div class="col-4">
                <div class="form-floating">
                    <input class="form-control" type="number" id="room" name="room" min="0" step="1" placeholder="Stanza" tabindex="-1" />
                    <label for="room">Stanza</label>
                </div>
            </div>

            <div class="col-4">
                <div class="form-floating">
                    <input class="form-control" type="number" id="umbrellas" name="umbrellas" required min="0" step="1" placeholder="Ombrelloni"/>
                    <label for="umbrellas">Ombrelloni</label>
                </div>
            </div>

            <div class="col-4">
                <div class="form-floating">
                    <input class="form-control" type="number" id="beds" name="beds" required min="0" step="1" placeholder="Lettini"/>
                    <label for="beds">Lettini</label>
                </div>
            </div>

            <div class="col-4">
                <div class="form-floating">
                    <input class="form-control" type="number" id="numDays" name="days" value="0" readonly tabindex="-1" />
                    <label class="form group has-float-label" for="numDays">Tot. Giorni</label>
                </div>
            </div>

            <input type="date" class="form-control form-control-sm" id="checkin" name="checkin" value="" required hidden />

            <input type="date" class="form-control form-control-sm" id="checkout" name="checkout" value="" required hidden />

            <script type="text/javascript" src="./js/datePicker.js"></script>


            <div class="col-2" id="confirmBsDiv">
                <input class="btn btn-primary btn-lg" id="confirmBs" type="submit" name="submit" value="Inserisci" />
            </div>

        </form>
    </div>
    <div class="col-4">
        <table class="table table-striped align-middle text-center table-hover" data-show-print="true">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Nome</th>
                    <th scope="col">Check-in</th>
                    <th scope="col">Check-out</th>
                    <th scope="col">Giorni</th>
                    <th scope="col">Ombr.</th>
                    <th scope="col">Lett.</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($templateParams["lastBS"] as $bs): ?>
                    <tr>
                        <th scope="row"><?php echo $bs["numBS"] ?></th>
                        <td><?php echo $bs["name"] ?></td>
                        <td><?php echo date("d/m", strtotime($bs["checkin"])) ?></td>
                        <td><?php echo date("d/m", strtotime($bs["checkout"])) ?></td>
                        <td><?php echo $bs["days"] ?></td>
                        <td><?php echo $bs["umbrellas"] ?></td>
                        <td><?php echo $bs["beds"] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
