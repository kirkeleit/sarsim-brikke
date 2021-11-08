<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="SAR Simulator Workspace">
    <title>SAR Simulator</title>
    <link href="/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <script src="/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="/js/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
  </head>
  <body>

<main class="container-fluid col-10">
<br />

<div class="card" id="CardBrikke">
  <div class="card-header h3 text-center" id="CardBrikkeHeader"><span id="BrikkeID">&nbsp;</span></div>

  <div class="card-body" id="OrganisasjonInfo" style="display:none;">
    <p class="h4 text-center">Denne SAR-brikken tilhører <span class="fw-bold" id="OrganisasjonNavn">&nbsp;</span>.</p>
    <hr />
    <p class="h5 text-center"><small class="text-muted">E-postadresse:</small><br /><span id="EpostAdresse">&nbsp;</span>
    <p class="h5 text-center"><small class="text-muted">Telefon:</small><br /><span id="Telefonnummer">&nbsp;</span></p>
  </div>

  <form id="PlasserBrikke" onsubmit="return KobleBrikke();">
  <div class="card-body" id="BrikkePlasser" style="display:none;">
    <hr>
    <input type="number" class="form-control form-control" name="Tilgangskode" id="Tilgangskode" placeholder="Tilgangskode" max="999999" required />
		<div class="form-check">
 			<input class="form-check-input" type="checkbox" value="" id="HuskTilgangskode" onchange="SlettHuskTilgangskode();">
 			<label class="form-check-label" for="HuskTilgangskode">Husk tilgangskoden på denne enheten</label>
    </div>
    <br />

    <input type="text" class="form-control form-control" name="Navn" id="Navn" placeholder="Taust vitne" maxlength="32" />
    <br />
    
    <div id="PosisjonsVarsler">
    </div>

    <div class="d-grid gap-2">
      <input type="submit" class="btn btn-primary btn-lg" id="BtnPlasserBrikke" value="Plasser brikke"  />
    </div>
  </div>
  </form>

  <div class="card-body" style="display:none;" id="ØvelseInfo">
    <p class="h5 text-center">Denne SAR-brikken er koblet opp mot en øvelse som ikke er startet enda.</p>
    <hr>
    <p class="h4 text-center"><small class="text-muted">Navn på øvelse:</small><br /><b><span id="ØvelseNavn">&nbsp;</span></b></p>
    <p class="h4 text-center"><small class="text-muted">Planlagt dato:</small><br /><b><span id="ØvelseDato">&nbsp;</span></b></p>
  </div>

  <div class="card-body" style="display:none;" id="BrikkeInfo">
    <p class="h1 text-center" id="BrikkeNavn">&nbsp;</p>
    <p class="h3 text-center" id="BrikkeMelding">&nbsp;</p>
  </div>

  <form id="Innsjekking" onsubmit="return SjekkInn();">
  <div class="card-body" id="BrikkeSjekkInn" style="display:none;">
    <div id="PosisjonsVarsler">
    </div>
    <div class="d-grid gap-2">
      <input type="submit" class="btn btn-success btn-lg" id="BtnSjekkInn" value="Funnet!"  />
    </div>
  </div>
  </form>

  <div class="card-body bg-warning" id="PosisjonDebug" style="display:none;">
    <p class="text-center">
    Latitude: <span id="Latitude">&nbsp;</span><br />
    Longitude: <span id="Longitude">&nbsp;</span><br />
    Accuracy: <span id="Accuracy">&nbsp;</span>
    </p>
  </div>

  <div class="card-footer text-center text-muted"><small>Lurer du på hva ei SAR-brikke er?<br /><a href="https://wiki.sar-simulator.no/index.php/SAR_Brikke" target="_new">Trykk her</a>.</small></div>
</div>

</main>

  </body>
</html>

<script>
  let BrikkeID = '<?php echo htmlspecialchars($_GET["id"]); ?>';
  let ØvelseID = '';
  let posLengdegrad = 0; // longitude
  let posBreddegrad = 0; // latitude
  let posNøyaktighet = 0;

  $(document).ready(function() {
	  console.log("Dokument lastet");
 	  HentBrikke();

    if (sessionStorage.getItem('Tilgangskode')) {
      $('#Tilgangskode').val(sessionStorage.getItem('Tilgangskode'));
      $("#HuskTilgangskode").prop('checked',true);
    }

    if (localStorage.getItem('PosisjonDebug',1)) {
      $('#PosisjonDebug').show();
    }

    if (navigator.geolocation) {
      navigator.geolocation.watchPosition(function(position) {
        $("#alertdiv").remove();
        $('#BtnPlasserBrikke').prop('disabled', false);
        $('#Latitude').text(position.coords.latitude);
        $('#Longitude').text(position.coords.longitude);
        $('#Accuracy').text(position.coords.accuracy);
        posLengdegrad = position.coords.longitude;
        posBreddegrad = position.coords.latitude;
        posNøyaktighet = position.coords.accuracy;
        if (position.coords.accuracy > 10) {
          $('#PosisjonsVarsler').append('<div id="alertdiv" class="alert alert-warning fade show text-center" role="alert">Posisjonsnøyaktighet er over 10 meter!</div>');
        }
      }, function(error) {
        $("#alertdiv").remove();
        if (error.code == 1) {
          $('#PosisjonsVarsler').append('<div id="alertdiv" class="alert alert-danger fade show text-center" role="alert">Posisjoneringsfeil: Ikke tilgang til posisjonsdata</div>');
        } else if (error.code == 2) {
          $('#PosisjonsVarsler').append('<div id="alertdiv" class="alert alert-secondary fade show text-center" role="alert">Vennligst vent på posisjon.</div>');
        } else if (error.code == 3) {
          $('#PosisjonsVarsler').append('<div id="alertdiv" class="alert alert-danger fade show text-center" role="alert">Posisjoneringsfeil: Tidsavbrudd ved henting av posisjon</div>');
        }
      }, { enableHighAccuracy: true, timeout: 20000, maximumAge: 1000 });
    } else {
      alert("Nettleseren din støtter ikke posisjonering. Du kan ikke bruke denne til å plassere ut brikker!");
    }
  });

  // Funksjon for å laste ned brikke fra API server.
  function HentBrikke() {
    console.log("Laster ned brikke '"+BrikkeID+"' fra server.");
    $.ajax({
      url:'https://api.sar-simulator.no/index.php/Brikker/brikke',
      type: 'POST',
      data: {
        'BrikkeID': BrikkeID
      },
      dataType: 'json',
			success: function(data) {
        console.log(data);
        document.title = 'SAR Simulator ['+BrikkeID+']';
				$('#BrikkeID').text(data.Brikke.BrikkeID);

        if (Array.isArray(data.KobletØvelse)) {
          if (data.KobletØvelse.length > 0) {
            // Brikken er koblet mot en øvelse.
            console.debug("Brikken er koblet mot en øvelse.");
            Øvelse = data.KobletØvelse[0];
            ØvelseID = Øvelse.ØvelseID;

            if (Øvelse.StatusID == 4) {
              // Øvelsen er pågående.
              console.debug("Øvelsen er pågående.");
              $('#CardBrikke').addClass('border-success');
              $('#CardBrikkeHeader').addClass('bg-success text-white');

              $('#BrikkeNavn').text(Øvelse.BrikkeNavn);
              $('#BrikkeMelding').text(Øvelse.BrikkeMelding);
              $('#BrikkeInfo').show();
              $('#BrikkeSjekkInn').show();
            } else {
              // Øvelsen er under planlegging eller klar.
              console.debug("Øvelsen er under planlegging eller klar.");
              $('#CardBrikke').addClass('border-primary');
              $('#CardBrikkeHeader').addClass('bg-primary text-white');

              $('#ØvelseNavn').text(Øvelse.Navn);
              $('#ØvelseDato').text(Øvelse.DatoPlanlagt);
              $('#ØvelseInfo').show();
            }
          } else {
            // Brikken er ikke koblet mot en øvelse.
            console.debug("Brikken er IKKE koblet mot en øvelse.");
            $('#CardBrikke').addClass('border-primary');
            $('#CardBrikkeHeader').addClass('bg-primary text-white');

            $('#OrganisasjonNavn').text(data.Organisasjon.Navn);
            $('#EpostAdresse').html('').html('<a href="mailto:'+data.Organisasjon.EpostAdresse+'">'+data.Organisasjon.EpostAdresse+'</a>');
            $('#Telefonnummer').html('').html('<a href="tel:'+data.Organisasjon.Telefonnummer+'">'+data.Organisasjon.Telefonnummer+'</a>');
            $('#OrganisasjonInfo').show();
            $('#BrikkePlasser').show();
          }
        }
      },
      error: function(xhr, ajaxOptions, thrownError) {
        alert('En feil oppstod! Prøv å last inn siden på nytt igjen.');
      }
    });
  }

  function KobleBrikke() {
    event.preventDefault();
    console.log("Kobler brikke '"+BrikkeID+"' mot øvelse med tilgangskode '"+$('#Tilgangskode').val()+"', og navngis '"+$('#Navn').val()+"'.")

    let Plassering = {};
    Plassering.BrikkeID = BrikkeID;
    Plassering.Tilgangskode = $('#Tilgangskode').val();
    Plassering.Navn = ($('#Navn').val().length > 0 ? $('#Navn').val() : 'Uten navn');
    Plassering.Lengdegrad = posLengdegrad;
    Plassering.Breddegrad = posBreddegrad;
    Plassering.Nøyaktighet = posNøyaktighet;

    $('#BtnPlasserBrikke').prop('disabled', true);
		$('#BtnPlasserBrikke').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
    $.ajax({
      url:'https://api.sar-simulator.no/index.php/Brikker/plasser',
      type: 'POST',
      data: Plassering,
      dataType: 'json',
			success: function(data) {
				console.log(data);
        if (data.Resultat == "OK") {
          window.location.href = "https://brikke.sar-simulator.no/?id="+BrikkeID;
        } else {
          alert(data.Melding);
        }
      },
      error: function(xhr, ajaxOptions, thrownError) {
        alert('En feil oppstod! Prøv å last inn siden på nytt igjen.');
      },
      complete: function(xhr, status) {
				$('#BtnPlasserBrikke').html('Lagre');
				$('#BtnPlasserBrikke').prop('disabled', false);
      }
    });

    if ($("#HuskTilgangskode").is(":checked")) {
      sessionStorage.setItem('Tilgangskode', $('#Tilgangskode').val());
    }
    return true;
  }

  function SlettHuskTilgangskode() {
    if (!$("#HuskTilgangskode").is(":checked")) {
      sessionStorage.removeItem('Tilgangskode');
    }
  }

  function SjekkInn() {
    event.preventDefault();

    $('#BtnSjekkInn').prop('disabled', true);
		$('#BtnSjekkInn').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
    $.ajax({
      url:'https://api.sar-simulator.no/index.php/Brikker/sjekkinn',
      type: 'POST',
      data: {
        'BrikkeID': BrikkeID,
        'ØvelseID': ØvelseID,
        'Lengdegrad': posLengdegrad,
        'Breddegrad': posBreddegrad,
        'Nøyaktighet': posNøyaktighet
      },
      dataType: 'json',
			success: function(data) {
				console.log(data);
        //window.location.href = "https://brikke.sar-simulator.no/?id="+BrikkeID;
        alert('Funn er nå registrert. Fortsett øvelsen.');
      },
      error: function(xhr, ajaxOptions, thrownError) {
        alert('En feil oppstod! Prøv å last inn siden på nytt igjen.');
      },
      complete: function(xhr, status) {
				$('#BtnSjekkInn').html('Funnet!');
				//$('#BtnSjekkInn').prop('disabled', false);
      }
    });

    return true;
  }
</script>
