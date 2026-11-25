$(document).ready(function () {
  if (!$("#select-municipio").length || typeof id_seguimiento === "undefined") {
    return;
  }

  var municipioSelect = $("#select-municipio").selectize({
    create: false,
    sortField: "text",
    placeholder: "Selecciona un municipio...",
  })[0].selectize;

  var coloniaSelect = $("#select-colonia").selectize({
    create: false,
    sortField: "text",
    placeholder: "Selecciona una colonia...",
  })[0].selectize;

  var programaSelect = $("#select-programa").selectize({
    create: false,
    sortField: "text",
    placeholder: "Selecciona un programa...",
  })[0].selectize;

  var cargandoColonias = false;

  function cargarColonias(idMunicipio, idColonia) {
    cargandoColonias = true;
    coloniaSelect.clear(true);
    coloniaSelect.clearOptions();
    coloniaSelect.disable();
    if (!idMunicipio) {
      coloniaSelect.addOption({ value: "", text: "Selecciona primero un municipio" });
      coloniaSelect.setValue("");
      cargandoColonias = false;
      return;
    }
    $.ajax({
      url: "/funciones/ubicaciones_func",
      type: "POST",
      dataType: "json",
      data: { action: "get_colonias", id_municipio: idMunicipio },
      success: function (data) {
        coloniaSelect.clearOptions();
        coloniaSelect.addOption({ value: "", text: "Selecciona una colonia" });
        if (data.length > 0) {
          $.each(data, function (i, row) {
            coloniaSelect.addOption({ value: row.id_colonia, text: row.nombre });
          });
          coloniaSelect.enable();
          if (idColonia && coloniaSelect.options.hasOwnProperty(idColonia)) {
            coloniaSelect.setValue(idColonia);
          }
        } else {
          coloniaSelect.disable();
        }
        cargandoColonias = false;
      },
    });
  }

  function cargarTiposAccion(idAmbito, seleccionado) {
    var tipo = $("#select-tipo_accion");
    tipo.empty().append('<option value="">Cargando...</option>').prop("disabled", true);
    if (!idAmbito) {
      tipo.empty().append('<option value="">Seleccione primero un ámbito</option>');
      return;
    }
    $.ajax({
      url: "/funciones/comites_func",
      type: "POST",
      dataType: "json",
      data: { action: "get-tipos", id_ambito: idAmbito },
      success: function (response) {
        tipo.empty().append('<option value="">Selecciona un tipo</option>');
        if (response.status === "ok") {
          $.each(response.data, function (i, row) {
            tipo.append('<option value="' + row.id_accion + '">' + row.descripcion + "</option>");
          });
          tipo.prop("disabled", false);
          if (seleccionado) {
            tipo.val(seleccionado);
          }
        }
      },
    });
  }

  municipioSelect.on("change", function (value) {
    if (!cargandoColonias) {
      cargarColonias(value, "");
    }
  });

  $("#select-ambito").on("change", function () {
    cargarTiposAccion($(this).val(), "");
  });

  programaSelect.on("change", function (value) {
    if (!value) {
      $("#input-ramo").val("");
      $("#input-instancia").val("");
      return;
    }
    $.ajax({
      url: "/funciones/comites_func",
      type: "POST",
      dataType: "json",
      data: { action: "get-programa", id_programa: value },
      success: function (response) {
        if (response.status === "ok") {
          var d = response.data;
          $("#input-ramo").val((d.ramo || "") + " - " + (d.descripcion || ""));
          $("#input-instancia").val(d.nombre_instancia || "");
        }
      },
    });
  });

  mostrarLoading();
  $.ajax({
    url: "/funciones/seguimiento_func",
    type: "POST",
    dataType: "json",
    data: { action: "ver", id_seguimiento: id_seguimiento },
    success: function (resp) {
      if (resp.status !== "ok") {
        mostrarAlerta("error", "No se encontró el seguimiento");
        window.location.href = "/seguimientos";
        return;
      }
      var s = resp.data;
      if (s.fk_id_municipio) {
        municipioSelect.setValue(String(s.fk_id_municipio));
        cargarColonias(String(s.fk_id_municipio), s.fk_id_colonia ? String(s.fk_id_colonia) : "");
      }
      if (s.fk_id_programa) {
        programaSelect.setValue(String(s.fk_id_programa));
      }
      $("#select-ambito").val(s.fk_id_ambito || "");
      cargarTiposAccion(s.fk_id_ambito, s.fk_id_accion);
      $("#fecha").val(s.fecha ? s.fecha.substr(0, 10) : "");
      $("#descripcion").val(s.descripcion || "");
      $("#comentarios").val(s.comentarios || "");
      $("#select-reunion").val(s.fk_id_tipo_reunion_seg || "");
      $("#select-modalidad").val(s.modalidad || "");
      $("#horario").val(s.horario || "");
      $("#beneficiarios_m").val(s.beneficiarios_m || "");
      $("#beneficiarios_h").val(s.beneficiarios_h || "");
      $("#beneficiarios").val(s.beneficiarios || "");
      ocultarLoading();
    },
    error: function () {
      ocultarLoading();
      mostrarAlerta("error", "Error al cargar el seguimiento");
    },
  });

  function guardarSeguimiento(alTerminar) {
    mostrarLoading();
    $.ajax({
      url: "/funciones/seguimiento_func",
      type: "POST",
      dataType: "json",
      data: {
        action: "editar",
        id_seguimiento: id_seguimiento,
        id_reunion_seguimiento: id_reunion_seguimiento,
        id_municipio: $("#select-municipio").val(),
        id_colonia: $("#select-colonia").val(),
        fecha: $("#fecha").val(),
        id_programa: $("#select-programa").val(),
        id_ambito: $("#select-ambito").val(),
        tipo_accion: $("#select-tipo_accion").val(),
        descripcion: $("#descripcion").val(),
        comentarios: $("#comentarios").val(),
        id_tipo_reunion: $("#select-reunion").val(),
        modalidad: $("#select-modalidad").val(),
        horario: $("#horario").val(),
        beneficiarios: $("#beneficiarios").val(),
        beneficiarios_m: $("#beneficiarios_m").val(),
        beneficiarios_h: $("#beneficiarios_h").val(),
      },
      success: function (resp) {
        ocultarLoading();
        if (resp.status === "ok") {
          if (alTerminar) {
            alTerminar();
          } else {
            mostrarAlerta("success", resp.message);
          }
        } else {
          mostrarAlerta("error", resp.message);
        }
      },
      error: function () {
        ocultarLoading();
        mostrarAlerta("error", "No se pudo guardar el seguimiento");
      },
    });
  }

  $("#form-seguimiento-editar").on("submit", function (e) {
    e.preventDefault();
    guardarSeguimiento();
  });

  function cambiarStatus(status) {
    mostrarLoading();
    $.ajax({
      url: "/funciones/seguimiento_func",
      type: "POST",
      dataType: "json",
      data: { action: "cambiar-status", id_seguimiento: id_seguimiento, status: status },
      success: function (resp) {
        ocultarLoading();
        if (resp.status === "ok") {
          window.location.reload();
        } else {
          mostrarAlerta("error", resp.message);
        }
      },
      error: function () {
        ocultarLoading();
      },
    });
  }

  function faltantesRevision() {
    var faltantes = [];
    if (!$("#fecha").val()) {
      faltantes.push("la fecha");
    }
    if (!$("#select-reunion").val()) {
      faltantes.push("el tipo de reunión");
    }
    if (!$("#select-modalidad").val()) {
      faltantes.push("la modalidad");
    }
    if (!$("#horario").val()) {
      faltantes.push("el horario");
    }
    return faltantes;
  }

  function mensajeFaltantes(faltantes) {
    var ultimo = faltantes[faltantes.length - 1];
    var lista = faltantes.length === 1 ? ultimo : faltantes.slice(0, -1).join(", ") + " y " + ultimo;
    return "No se puede mandar a revisión. Falta " + lista + ".";
  }

  $("#btnRevision").on("click", function () {
    var faltantes = faltantesRevision();
    if (faltantes.length) {
      mostrarAlerta("error", mensajeFaltantes(faltantes));
      return;
    }
    Swal.fire({
      title: "¿Mandar a revisión este seguimiento?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, mandar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#ff8400",
    }).then(function (result) {
      if (!result.isConfirmed) {
        return;
      }
      if (puedeEditarSeguimiento) {
        guardarSeguimiento(function () {
          cambiarStatus(1);
        });
      } else {
        cambiarStatus(1);
      }
    });
  });
  $("#btnFinalizar").on("click", function () {
    Swal.fire({
      title: "¿Finalizar este seguimiento?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, finalizar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#ff8400",
    }).then(function (result) {
      if (result.isConfirmed) {
        cambiarStatus(2);
      }
    });
  });
  var tablaCapacitadores = $("#tabla_capacitadores").DataTable({
    dom: "Brtip",
    ajax: {
      url: "/funciones/seguimiento_func",
      type: "POST",
      data: {
        action: "listar-capacitadores",
        id_reunion_seguimiento: id_reunion_seguimiento,
      },
    },
    autoWidth: false,
    columnDefs: [
      { targets: 0, visible: false, searchable: false },
      { width: "30%", targets: 1 },
      { width: "60%", targets: 2 },
      { width: "10%", targets: 3, visible: puedeEditarSeguimiento },
    ],
    paging: false,
    processing: true,
    serverSide: false,
    language: {
      sProcessing: "<img src='./assets/img/leon.png' style='height:100px;'/>",
      sLoadingRecords: "<div style='height:150px'></div>",
      zeroRecords: "No hay capacitadores asignados",
    },
  });

  function actualizarBotonesPaso() {
    var enPaso3 = Number($(".step.active").data("step")) === 3;
    $("#btnRevision, #btnFinalizar, #btnLiberar").toggleClass("d-none", !enPaso3);
  }

  $("#nextBtn, #prevBtn, .progressbar li").on("click", function () {
    setTimeout(function () {
      tablaCapacitadores.columns.adjust();
      actualizarBotonesPaso();
    }, 50);
  });

  $("#btn-agregar-capacitador").on("click", function () {
    var idUsuario = $("#select-usuario-capacitador").val();
    if (!idUsuario) {
      mostrarAlerta("error", "Por favor selecciona un usuario.");
      return;
    }
    $.ajax({
      url: "/funciones/seguimiento_func",
      type: "POST",
      dataType: "json",
      data: {
        action: "agregar-capacitador",
        id_usuario: idUsuario,
        id_reunion_seguimiento: id_reunion_seguimiento,
      },
      success: function (resp) {
        if (resp.status === "ok") {
          mostrarAlerta("success", resp.message);
          $("#select-usuario-capacitador").val("");
          tablaCapacitadores.ajax.reload();
        } else {
          mostrarAlerta("error", resp.message);
        }
      },
      error: function () {
        mostrarAlerta("error", "No se pudo agregar el capacitador");
      },
    });
  });

  $("#capacitadores-seguimiento").on("click", ".delete-capacitador", function (e) {
    e.preventDefault();
    var idCapacitador = $(this).data("id");
    if (!idCapacitador) {
      mostrarAlerta("error", "Capacitador no encontrado");
      return;
    }
    Swal.fire({
      title: "¿Quitar a este capacitador?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, quitar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#ff8400",
    }).then(function (result) {
      if (!result.isConfirmed) {
        return;
      }
      $.ajax({
        url: "/funciones/seguimiento_func",
        type: "POST",
        dataType: "json",
        data: {
          action: "eliminar-capacitador",
          id_seguimiento_capacitador: idCapacitador,
        },
        success: function (resp) {
          if (resp.status === "ok") {
            mostrarAlerta("success", resp.message);
            tablaCapacitadores.ajax.reload();
          } else {
            mostrarAlerta("error", resp.message);
          }
        },
        error: function () {
          mostrarAlerta("error", "No se pudo quitar el capacitador");
        },
      });
    });
  });

  if ($("#tabla_permiso_seguimiento").length) {
    var tablaPermiso = $("#tabla_permiso_seguimiento").DataTable({
      dom: "Brtip",
      ajax: {
        url: "/funciones/seguimiento_func",
        type: "POST",
        data: {
          action: "listar-permiso",
          id_seguimiento: id_seguimiento,
        },
      },
      autoWidth: false,
      columnDefs: [
        { targets: 0, visible: false, searchable: false },
        { width: "80%", targets: 1 },
        { width: "20%", targets: 2 },
      ],
      paging: false,
      processing: true,
      serverSide: false,
      language: {
        sProcessing: "<img src='./assets/img/leon.png' style='height:100px;'/>",
        sLoadingRecords: "<div style='height:150px'></div>",
        zeroRecords: "No hay usuarios asignados",
      },
    });

    $("#btn-agregar-permiso-seguimiento").on("click", function () {
      var idUsuario = $("#select-usuario-permiso").val();
      if (!idUsuario) {
        mostrarAlerta("error", "Por favor selecciona un usuario.");
        return;
      }
      $.ajax({
        url: "/funciones/seguimiento_func",
        type: "POST",
        dataType: "json",
        data: {
          action: "agregar-permiso",
          id_usuario: idUsuario,
          id_seguimiento: id_seguimiento,
        },
        success: function (resp) {
          if (resp.status === "ok") {
            mostrarAlerta("success", resp.message);
            $("#select-usuario-permiso").val("");
            tablaPermiso.ajax.reload();
          } else {
            mostrarAlerta("error", resp.message);
          }
        },
        error: function () {
          mostrarAlerta("error", "No se pudo agregar el usuario");
        },
      });
    });

    $("#usuarios-permiso-seguimiento").on("click", ".delete-permiso-seguimiento", function (e) {
      e.preventDefault();
      var idPermiso = $(this).data("id");
      Swal.fire({
        title: "¿Quitar este permiso?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, quitar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#ff8400",
      }).then(function (result) {
        if (!result.isConfirmed) {
          return;
        }
        $.ajax({
          url: "/funciones/seguimiento_func",
          type: "POST",
          dataType: "json",
          data: {
            action: "eliminar-permiso",
            id_seguimiento_usuario: idPermiso,
          },
          success: function (resp) {
            if (resp.status === "ok") {
              mostrarAlerta("success", resp.message);
              tablaPermiso.ajax.reload();
            } else {
              mostrarAlerta("error", resp.message);
            }
          },
          error: function () {
            mostrarAlerta("error", "No se pudo quitar el permiso");
          },
        });
      });
    });
  }

  function mostrarDocumento() {
    $.ajax({
      url: "/funciones/seguimiento_func",
      type: "POST",
      dataType: "json",
      data: { action: "mostrar-doc", id_seguimiento: id_seguimiento },
      success: function (data) {
        var contenedor = $("#documento-preview");
        if (data.status === "ok" && data.ruta) {
          var boton = puedeEditarSeguimiento
            ? '<button type="button" class="d-flex align-items-center btn btn-danger eliminar-doc ms-auto mt-3">Eliminar documento <span class="material-icons">delete</span></button>'
            : "";
          contenedor.html('<iframe src="' + data.ruta + '" width="100%" height="900px" style="border:none;"></iframe>' + boton);
          $("#doc-seguimiento").addClass("d-none");
        } else {
          contenedor.html("<p>No se ha subido el documento</p>");
          if (puedeEditarSeguimiento) {
            $("#doc-seguimiento").removeClass("d-none");
          }
        }
      },
    });
  }

  mostrarDocumento();

  $("#documento-seguimiento").on("change", function (e) {
    var file = e.target.files[0];
    if (!file) {
      return;
    }
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
    var formData = new FormData();
    formData.append("documento", file);
    formData.append("action", "agregar-doc");
    formData.append("id_seguimiento", id_seguimiento);
    $.ajax({
      url: "/funciones/seguimiento_func",
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (data) {
        if (data.status === "ok") {
          mostrarAlerta("success", "El documento se subió correctamente.");
          mostrarDocumento();
          e.target.value = "";
        } else {
          mostrarAlerta("error", data.message || "Error al subir el documento.");
        }
      },
      error: function () {
        mostrarAlerta("error", "Error al subir el archivo.");
      },
    });
  });

  $("#documento-preview").on("click", ".eliminar-doc", function () {
    Swal.fire({
      title: "¿Eliminar el documento?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Eliminar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#ff8400",
      cancelButtonColor: "#d33",
    }).then(function (result) {
      if (!result.isConfirmed) {
        return;
      }
      $.ajax({
        url: "/funciones/seguimiento_func",
        type: "POST",
        dataType: "json",
        data: { action: "eliminar-doc", id_seguimiento: id_seguimiento },
        success: function (resp) {
          if (resp.status === "ok") {
            mostrarAlerta("success", resp.message);
            mostrarDocumento();
          } else {
            mostrarAlerta("error", resp.message);
          }
        },
        error: function () {
          mostrarAlerta("error", "No se pudo eliminar el documento");
        },
      });
    });
  });

  $("#btnLiberar").on("click", function () {
    Swal.fire({
      title: "¿Liberar este seguimiento?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, liberar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#ff8400",
    }).then(function (result) {
      if (result.isConfirmed) {
        cambiarStatus(0);
      }
    });
  });

  if ($("#modalOrigenSeguimiento").length && typeof comitesOrigen !== "undefined") {
    var comiteOrigenSelect = null;

    function llenarComitesOrigen() {
      if (!comiteOrigenSelect) {
        return;
      }
      var year = String($("#select-year-origen").val());
      var actual = comiteOrigenSelect.getValue();
      comiteOrigenSelect.clear(true);
      comiteOrigenSelect.clearOptions();
      comiteOrigenSelect.addOption({ value: "", text: "Busca por folio" });
      comitesOrigen.forEach(function (comite) {
        if (String(comite.year) !== year) {
          return;
        }
        var texto = comite.folio || "";
        if (comite.municipio) {
          texto += " — " + comite.municipio;
        }
        comiteOrigenSelect.addOption({ value: String(comite.id_comite), text: texto });
      });
      comiteOrigenSelect.refreshOptions(false);
      if (actual && comiteOrigenSelect.options[actual]) {
        comiteOrigenSelect.setValue(actual, true);
      }
    }

    function mostrarComiteOrigen(visible) {
      if (visible) {
        $("#bloque-comite-origen").removeClass("d-none");
        if (!comiteOrigenSelect) {
          comiteOrigenSelect = $("#select-comite-origen").selectize({
            create: false,
            sortField: "text",
            placeholder: "Busca por folio...",
            dropdownParent: "body",
          })[0].selectize;
        }
        llenarComitesOrigen();
      } else {
        $("#bloque-comite-origen").addClass("d-none");
        if (comiteOrigenSelect) {
          comiteOrigenSelect.clear(true);
        }
      }
    }

    function restaurarOrigen() {
      var origen = comiteSeguimiento ? "registrado" : "independiente";
      $('#modalOrigenSeguimiento input[name="origen"][value="' + origen + '"]').prop("checked", true);
      $("#select-year-origen").val(String(anioSeguimiento));
      mostrarComiteOrigen(origen === "registrado");
      if (comiteOrigenSelect && comiteSeguimiento && comiteOrigenSelect.options[String(comiteSeguimiento)]) {
        comiteOrigenSelect.setValue(String(comiteSeguimiento), true);
      }
    }

    $("#modalOrigenSeguimiento").on("shown.bs.modal", restaurarOrigen);
    $('#modalOrigenSeguimiento input[name="origen"]').on("change", function () {
      mostrarComiteOrigen($(this).val() === "registrado");
    });
    $("#select-year-origen").on("change", llenarComitesOrigen);

    $("#form-origen-seguimiento").on("submit", function (e) {
      e.preventDefault();
      var origen = $('#modalOrigenSeguimiento input[name="origen"]:checked').val();
      var idComite = origen === "registrado" && comiteOrigenSelect ? comiteOrigenSelect.getValue() : "";
      if (origen === "registrado" && !idComite) {
        mostrarAlerta("warning", "Selecciona el folio del comité.");
        return;
      }
      mostrarLoading();
      $.ajax({
        url: "/funciones/seguimiento_func",
        type: "POST",
        dataType: "json",
        data: {
          action: "editar-origen",
          id_seguimiento: id_seguimiento,
          origen: origen,
          year: $("#select-year-origen").val(),
          id_comite: idComite,
        },
        success: function (resp) {
          ocultarLoading();
          if (resp.status === "ok") {
            window.location.reload();
          } else {
            mostrarAlerta("error", resp.message || "No se pudo actualizar el enlace del comité.");
          }
        },
        error: function () {
          ocultarLoading();
          mostrarAlerta("error", "No se pudo actualizar el enlace del comité.");
        },
      });
    });
  }
});
