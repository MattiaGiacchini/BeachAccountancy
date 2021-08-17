<div class="row" id="mainRow">
    <div class="col-8">
        <?php var_dump($templateParams["bs"]); ?>
        <form class="row g-3 card-block bg-faded" action="#" id="formBS" method="post">

            <div class="col-4">
                <div class="form-floating">
                    <input class="form-control" type="number" id="numBS" name="numBS" required autofocus min="0" step="1" placeholder="Numero BS" value="<?php echo $templateParams["bs"]["numBS"]; ?>"/>
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
                    <input class="form-control" type="text" id="clientName" name="name" required placeholder="Nome Ospite" value="<?php echo $templateParams["bs"]["name"]; ?>">
                    <label for="clientName">Nome Ospite</label>
                </div>
            </div>

            <div class="col-4">
                <div class="form-floating">
                    <input class="form-control" type="number" id="room" name="room" min="0" step="1" placeholder="Stanza" tabindex="-1" value="<?php echo $templateParams["bs"]["room"]; ?>"/>
                    <label for="room">Stanza</label>
                </div>
            </div>

            <div class="col-4">
                <div class="form-floating">
                    <input class="form-control" type="number" id="umbrellas" name="umbrellas" required min="0" step="1" placeholder="Ombrelloni" value="<?php echo $templateParams["bs"]["umbrellas"]; ?>"/>
                    <label for="umbrellas">Ombrelloni</label>
                </div>
            </div>

            <div class="col-4">
                <div class="form-floating">
                    <input class="form-control" type="number" id="beds" name="beds" required min="0" step="1" placeholder="Lettini" value="<?php echo $templateParams["bs"]["beds"]; ?>"/>
                    <label for="beds">Lettini</label>
                </div>
            </div>

            <div class="col-4">
                <div class="form-floating">
                    <input class="form-control" type="number" id="numDays" name="days" value="0" readonly tabindex="-1" />
                    <label class="form group has-float-label" for="numDays">Tot. Giorni</label>
                </div>
            </div>

            <input type="date" class="form-control form-control-sm" id="checkin" name="checkin" required hidden value="<?php echo $templateParams["bs"]["checkin"]; ?>" />

            <input type="date" class="form-control form-control-sm" id="checkout" name="checkout" required hidden value="<?php echo $templateParams["bs"]["checkout"]; ?>" />

            <script type="text/javascript" src="./js/datePicker.js"></script>


            <div class="col-2" id="confirmBsDiv">
                <input class="btn btn-primary btn-lg" id="confirmBs" type="submit" name="submit" value="Aggiorna" />
            </div>

        </form>
    </div>

</div>
