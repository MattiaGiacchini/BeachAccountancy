<div class="row" id="mainRow">
    <div class="col-8">

        <form class="row g-3 card-block bg-faded" action="#" id="formPeriod" method="post">

            <div class="col-7">
                <div class="form-floating">
                    <input class="form-control" type="text" id="periodName" name="periodName" value="" placeholder="Nome Periodo" tabindex="-1" />
                    <label for="periodName">Nome Periodo</label>
                </div>
            </div>

            <div class="col-4">
                <div class="form-floating">
                    <input class="form-control" type="number" id="price" name="price" autofocus required min="0" step="0.1" placeholder="Prezzo Giornaliero" />
                    <label for="price">Prezzo Giornaliero</label>
                </div>
            </div>

            <input type="date" id="checkin" name="datein" value="" required hidden />
            <input type="date" id="checkout" name="dateout" value="" required hidden />

            <script type="text/javascript" src="./js/datePicker.js"></script>


            <div class="col-2" id="confirmPeriodDiv">
                <input class="btn btn-primary btn-lg" id="confirmBs" type="submit" name="submit" value="Inserisci" />
            </div>

        </form>
    </div>

</div>
