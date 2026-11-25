var columnaVisible = statusComite === 0;

document.getElementById("imagen").addEventListener("change", function (e) {
  var fileInput = e.target;
  var files = fileInput.files;
  var maxArchivos = 6;
  var maxSize = 10 * 1024 * 1024; // 10MB en bytes

  fetch("/funciones/uploads_func", {
    method: "POST",
    body: new URLSearchParams({
      action: "contar-imagenes",
      id_comite: id_comite,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status !== "ok") {
        mostrarAlerta("error", data.message || "No se pudo contar imágenes");
        return;
      }
      const totalExistentes = data.cantidad || 0;

      if (totalExistentes >= maxArchivos) {
        mostrarAlerta(
          "error",
          "Ya hay 6 imágenes en este comité, elimina alguna para agregar más."
        );
        fileInput.value = "";
        return;
      }

      if (totalExistentes + files.length > maxArchivos) {
        mostrarAlerta(
          "error",
          "Solo puedes agregar " +
            (maxArchivos - totalExistentes) +
            " imagen(es) más para este comité."
        );
        fileInput.value = "";
        return;
      }

      for (let i = 0; i < files.length; i++) {
        let f = files[i];
        if (!["image/jpeg", "image/png"].includes(f.type)) {
          mostrarAlerta("error", "Formato de imagen no permitido: " + f.name);
          fileInput.value = "";
          return;
        }
        if (f.size > maxSize) {
          mostrarAlerta(
            "error",
            'La imagen "' + f.name + '" supera el tamaño máximo de 10MB'
          );
          fileInput.value = "";
          return;
        }
      }

      let subidas = 0;
      let rechazadas = 0;

      for (let i = 0; i < files.length; i++) {
        let formData = new FormData();
        formData.append("imagen", files[i]);
        formData.append("action", "agregar-imagen");
        formData.append("id_comite", id_comite);

        fetch("/funciones/uploads_func", {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.status === "ok" || data.success) {
              subidas++;
            } else {
              rechazadas++;
              mostrarAlerta("error", data.message);
            }
            // Cuando termine el último fetch, recarga galería
            if (subidas + rechazadas === files.length) {
              cargarImagenesPorComite(id_comite);
              fileInput.value = "";
              mostrarAlerta("success", "Imagen subida correctamente");
            }
          })
          .catch((error) => {
            mostrarAlerta("error", "Error en la subida");
            console.error(error);
          });
      }
    });
});

let rutasImagenes = [];

function cargarImagenesPorComite(idComite) {
  var formData = new FormData();
  formData.append("action", "listar-imagenes");
  formData.append("id_comite", idComite);

  fetch("/funciones/uploads_func", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      const contenedor = $("#imagenes");
      contenedor.empty();
      rutasImagenes = [];

      if (data.status === "ok" && data.imagenes.length > 0) {
        data.imagenes.forEach((ruta) => {
          rutasImagenes.push(ruta);

          // Crear miniatura como antes
          let wrapper = $("<div>").addClass(
            "imagen-wrapper d-inline-block position-relative me-3"
          );
          let img = $("<img>")
            .attr("src", ruta)
            .attr("alt", "Imagen comité")
            .css({ maxWidth: "150px", display: "block" });
          wrapper.append(img);

          if (statusComite === 0) {
            let btn = $("<button>")
              .addClass("material-icons delete-imagen position-absolute")
              .text("delete")
              .attr("title", "Eliminar imagen")
              .css({
                top: "5px",
                right: "5px",
                display: "none",
                background: "rgba(255,132,0,0.9)",
                border: "1px solid #ccc",
                color: "white",
                borderRadius: "10%",
                padding: "3px 6px",
                cursor: "pointer",
                zIndex: 2,
              })
              .attr("type", "button")
              .attr("data-ruta", ruta)
              .on("click", function () {
                eliminarImagen(idComite, ruta);
              });

            wrapper.hover(
              () => btn.show(),
              () => btn.hide()
            );
            wrapper.append(btn);
          }
          contenedor.append(wrapper);
        });
        $("#abrirGaleriaBtn").show();
      } else {
        contenedor.html("<p>No hay imágenes para este comité</p>");
        $("#abrirGaleriaBtn").hide();
      }
    })
    .catch((error) => {
      console.error(error);
      mostrarAlerta("error", "Error de conexión al cargar imágenes");
    });
}

$("#abrirGaleriaBtn").click(function () {
  let modalBody = $("#modalGaleria .modal-body");
  modalBody.empty();

  rutasImagenes.forEach((ruta) => {
    let wrapper = $("<div>").addClass("position-relative");
    let img = $("<img>")
      .attr("src", ruta)
      .css({ maxHeight: "80vh", maxWidth: "80vw", borderRadius: "8px" });

    let botonDescarga = $("<a>")
      .attr("href", ruta)
      .attr("download", ruta.split("/").pop())
      .attr("title", "Descargar imagen")
      .addClass("btn btn-sm btn-light position-absolute top-0 end-0 m-2")
      .html('<i class="bi bi-download"></i>'); // Usar icono Bootstrap Icons si tienes

    wrapper.append(img).append(botonDescarga);
    modalBody.append(wrapper);
  });

  // Abrir modal con Bootstrap JS
  var modal = new bootstrap.Modal(document.getElementById("modalGaleria"), {});
  modal.show();
});

cargarImagenesPorComite(id_comite);

$("#imagenes").on("click", ".delete-imagen", function (e) {
  e.preventDefault();
  const ruta = $(this).data("ruta");

  if (!ruta) {
    mostrarAlerta("error", "Imagen no encontrada");
    return;
  }

  Swal.fire({
    title: "¿Deseas eliminar esta imagen?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Eliminar",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#ff8400",
    cancelButtonColor: "#d33",
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "/funciones/uploads_func", // Ajusta ruta correcta
        type: "POST",
        dataType: "json",
        data: {
          action: "delete-imagen",
          ruta: ruta,
          id_comite: id_comite,
        },
        success: function (response) {
          if (response.status === "ok") {
            mostrarAlerta("success", response.message);
            cargarImagenesPorComite(id_comite);
          } else {
            mostrarAlerta("error", response.message);
          }
        },
        error: function () {
          mostrarAlerta(
            "error",
            "Error al realizar la petición de eliminación"
          );
        },
      });
    }
  });
});

function mostrarActaPorComite(id_comite) {
  var formData = new FormData();
  formData.append("action", "mostrar-acta");
  formData.append("id_comite", id_comite);

  fetch("/funciones/uploads_func", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      const contenedor = document.getElementById("acta-preview");
      contenedor.innerHTML = ""; // Limpiar antes

      if (data.status === "ok" && data.ruta) {
        if (statusComite === 0) {
          contenedor.innerHTML = `
                <iframe src="${data.ruta}" width="100%" height="900px" style="border:none;"></iframe>
                <button data-ruta="${data.ruta}" type="button" class="d-flex align-items-center btn btn-danger eliminar-acta ms-auto mt-3">Eliminar acta <span class="material-icons">delete</span></button>
            `;
        } else {
          contenedor.innerHTML = `
                <iframe src="${data.ruta}" width="100%" height="900px" style="border:none;"></iframe>
            `;
        }
      } else {
        contenedor.innerHTML = "<p>No se ha subido ninguna acta</p>";
      }
    })
    .catch((error) => {
      console.error(error);
      document.getElementById("acta").innerHTML =
        "<p>Error al cargar el acta PDF</p>";
    });
}

mostrarActaPorComite(id_comite);

document.getElementById("acta").addEventListener("change", function (e) {
  const file = e.target.files[0];

  // Validar archivo
  if (!file) return;

  if (file.type !== "application/pdf") {
    mostrarAlerta("error", "Selecciona un archivo PDF válido.");
    e.target.value = "";
    return;
  }
  if (file.size > 10 * 1024 * 1024) {
    mostrarAlerta("error", "El PDF no debe pesar más de 10MB.");
    e.target.value = "";
    return;
  }

  // Opcional: mostrar preview temporal antes de subir
  const tempURL = URL.createObjectURL(file);
  document.getElementById("acta-preview").innerHTML = `
        <iframe src="${tempURL}" width="100%" height="400px" style="border:none;"></iframe>
    `;

  // Subir PDF por AJAX
  const formData = new FormData();
  formData.append("acta", file);
  formData.append("action", "agregar-acta");
  formData.append("id_comite", id_comite); // Usa tu variable real

  fetch("/funciones/uploads_func", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "ok" && data.ruta) {
        mostrarAlerta("success", "El acta PDF se subió correctamente.");
        // Mostrar PDF oficial desde el servidor
        mostrarActaPorComite(id_comite);
        e.target.value = "";
      } else {
        mostrarAlerta("error", data.message || "Error al subir el acta PDF.");
      }
    })
    .catch((error) => {
      mostrarAlerta("error", "Error al subir el archivo.");
      console.error(error);
    });
});

$("#acta-preview").on("click", ".eliminar-acta", function (e) {
  e.preventDefault();
  const ruta = $(this).data("ruta");

  if (!ruta) {
    mostrarAlerta("error", "Acta no encontrada");
    return;
  }

  Swal.fire({
    title: "¿Deseas eliminar la acta?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Eliminar",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#ff8400",
    cancelButtonColor: "#d33",
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "/funciones/uploads_func", // Ajusta ruta correcta
        type: "POST",
        dataType: "json",
        data: {
          action: "eliminar-acta",
          ruta: ruta,
          id_comite: id_comite,
        },
        success: function (response) {
          if (response.status === "ok") {
            mostrarAlerta("success", response.message);
            mostrarActaPorComite(id_comite);
          } else {
            mostrarAlerta("error", response.message);
          }
        },
        error: function () {
          mostrarAlerta(
            "error",
            "Error al realizar la petición de eliminación"
          );
        },
      });
    }
  });
});
