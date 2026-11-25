var miElemento = document.querySelector(".selectize-input");

if (miElemento) {
  miElemento.classList.remove("form-control");
}

function mostrarAlerta(tipo, mensaje, titulo = "", tiempo = 5500) {
  /*
    tipo: 'success', 'error', 'warning', 'info', 'question'
    mensaje: texto del cuerpo
    titulo: opcional, título de la alerta
    tiempo: milisegundos para que se cierre automáticamente (0 para que no cierre)
    */
  Swal.fire({
    icon: tipo,
    title:
      titulo ||
      (tipo === "success"
        ? "¡Éxito!"
        : tipo.charAt(0).toUpperCase() + tipo.slice(1)),
    text: mensaje,
    timer: tiempo > 0 ? tiempo : undefined,
    showConfirmButton: tiempo > 0 ? false : true,
    timerProgressBar: tiempo > 0 ? true : false,
    position: "center",
  });
}

//Formulario por pasos
let currentStep = 0;
const steps = $(".step").length;

function showStep(n) {
  $(".step").removeClass("active").eq(n).addClass("active");
  $(".progressbar li")
    .removeClass("active")
    .each(function (index) {
      if (index <= n) $(this).addClass("active");
    });

  if (n === 0) {
    $("#prevBtn").hide();
  } else {
    $("#prevBtn").show();
  }

  if (n === steps - 1) {
    $("#nextBtn").hide();
    $("#actualizar").hide();
    $("#submitBtn").show();
    $("#revisionBtn").show();
  } else {
    $("#nextBtn").show();
    $("#submitBtn").hide();
  }
}

$(document).ready(function () {
  // Aplica formateo en tiempo real
  $("input[data-type='currency']").on({
    keyup: function () {
      formatCurrency($(this));
      actualizarValorLimpio($(this));
    },
    blur: function () {
      formatCurrency($(this), "blur");
      actualizarValorLimpio($(this));
    },
  });

  function formatNumber(n) {
    return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }

  function formatCurrency(input, blur) {
    var input_val = input.val();

    if (input_val === "") {
      return;
    }

    var original_len = input_val.length;
    var caret_pos = input.prop("selectionStart");

    if (input_val.indexOf(".") >= 0) {
      var decimal_pos = input_val.indexOf(".");
      var left_side = input_val.substring(0, decimal_pos);
      var right_side = input_val.substring(decimal_pos);

      left_side = formatNumber(left_side);
      right_side = formatNumber(right_side);

      if (blur === "blur") {
        right_side += "00";
      }

      right_side = right_side.substring(0, 2);

      input_val = "$" + left_side + "." + right_side;
    } else {
      input_val = formatNumber(input_val);
      input_val = "$" + input_val;

      if (blur === "blur") {
        input_val += ".00";
      }
    }

    input.val(input_val);

    var updated_len = input_val.length;
    caret_pos = updated_len - original_len + caret_pos;
    input[0].setSelectionRange(caret_pos, caret_pos);
  }

  // Actualiza el campo oculto con el valor numérico limpio para enviar
  function actualizarValorLimpio($inputVisible) {
    var valFormateado = $inputVisible.val(); // ejemplo: $5,343,553.32
    // Elimina todo excepto dígitos y punto decimal
    var valorLimpio = valFormateado.replace(/[^0-9.]/g, "");
    $("#inversion").val(valorLimpio);
  }

  // Inicializa el campo oculto si ya hay valor visible
  var initialVal = $("#inversionFormat").val();
  if (initialVal) {
    actualizarValorLimpio($("#inversionFormat"));
  }

  // Opcional: capturar submit para asegurar enviar valor limpio
  $("#formulario-inversion").on("submit", function () {
    actualizarValorLimpio($("#inversionFormat"));
  });
});

showStep(currentStep);

$("#nextBtn").on("click", function () {
  if (currentStep < steps - 1) {
    currentStep++;
    showStep(currentStep);
  }
});

$("#prevBtn").on("click", function () {
  if (currentStep > 0) {
    currentStep--;
    showStep(currentStep);
  }
});

function showStep(n) {
  $(".step").removeClass("active").eq(n).addClass("active");

  $(".progressbar li")
    .removeClass("active")
    .each(function (index) {
      if (index <= n) $(this).addClass("active");
    });

  const percentage = (n / (steps - 1)) * 100;
  $("#progress-fill").css("width", `${percentage}%`); // Botones

  $("#prevBtn").toggle(n !== 0);
  $("#nextBtn").toggle(n !== steps - 1);
  $("#submitBtn").toggle(n === steps - 1);
  $("#revisionBtn").toggle(n === steps - 1);
}

$(".progressbar li").on("click", function () {
  const index = $(this).index(); // Obtener el índice del li clicado
  currentStep = index; // Actualizar el paso actual
  showStep(currentStep); // Mostrar el paso correspondiente
});

function formatNumber(number, tipo) {
  if (tipo === "porcentaje") {
    if (number > 100) {
      return "100%";
    } else {
      return Math.floor(number) + "%";
    }
  } else if (tipo === "redondeo") {
    if (number >= 1000) {
      var suffix = "";
      if (number < 1000000) {
        number = Math.floor(number / 1000);
        suffix = "K";
      } else if (number < 1000000000) {
        number = Math.floor(number / 1000000);
        suffix = "M";
      } else {
        number = Math.floor(number / 1000000000);
        suffix = "B";
      }
      return number + suffix + "+";
    } else {
      return number;
    }
  } else {
    if (number >= 1000) {
      var suffix = "";
      if (number < 1000000) {
        number = (number / 1000).toFixed(1).replace(/\.0$/, ""); // Eliminar el ".0" si no hay decimales significativos
        suffix = "K";
      } else if (number < 1000000000) {
        number = (number / 1000000).toFixed(1).replace(/\.0$/, ""); // Eliminar el ".0" si no hay decimales significativos
        suffix = "M";
      } else {
        number = (number / 1000000000).toFixed(1).replace(/\.0$/, ""); // Eliminar el ".0" si no hay decimales significativos
        suffix = "B";
      }
      return number + suffix;
    } else {
      return number;
    }
  }
}

function lanza_conteo(entries, observer) {
  entries.forEach(function (entry) {
    if (entry.isIntersecting) {
      var container = entry.target;
      var elements = container.querySelectorAll(".contador");
      var porcentaje = container.querySelectorAll(".es_porcentaje");
      var redondeo = container.querySelectorAll(".es_redondeado");

      var tipo =
        porcentaje.length > 0
          ? "porcentaje"
          : redondeo.length > 0
          ? "redondeo"
          : undefined;
      console.log(tipo);
      elements.forEach(function (element) {
        var startCount = 0;
        var targetCount = parseInt(element.dataset.value);
        var duration = 4000;
        var startTime = null;

        function updateCounter(timestamp) {
          if (!startTime) startTime = timestamp;
          var progress = timestamp - startTime;
          var increment = Math.floor(
            (progress / duration) * (targetCount - startCount)
          );

          if (progress < duration) {
            element.textContent = formatNumber(startCount + increment, tipo);
            requestAnimationFrame(updateCounter);
          } else {
            element.textContent = formatNumber(targetCount, tipo);
          }
        }

        requestAnimationFrame(updateCounter);
      });

      observer.unobserve(container); // Deja de observar este contenedor una vez que se activa
    }
  });
}

// Llamada inicial al cargar la página
//lanza_conteo();

var options = {
  threshold: 0.5, // Cuando al menos el 50% del elemento es visible
};

var observer = new IntersectionObserver(lanza_conteo, options);

var containers = document.querySelectorAll(".conteo_actividades");
containers.forEach(function (container) {
  observer.observe(container);
});
