<script>
   document.addEventListener('DOMContentLoaded', function() {
    const inviteBtn = document.getElementById('studentSelector');
    if (!inviteBtn) return;
    inviteBtn.addEventListener('click', function(e) {
      e.preventDefault();

      const code = this.getAttribute('data-code');

      Swal.fire({
        title: 'Arkadaşını Davet Et',
        html: `
          <div style="text-align:center; line-height:1.6;">
            <p>Arkadaşına <strong>${code}</strong> kodunu gönder.</p>
            <p>Seçtiği EVO Eğitim paketlerinden birini satın alırken, indirim kodu alanına <strong>senin kodunu</strong> yazsın.</p>
            <p><strong>4 adet ek ders kredisi</strong> kazansın!</p>
            <p style="font-size:0.9em; color:#666; margin-top:1em;">
              İlgili kampanya <strong>her kullanıcı için bir kez</strong> geçerlidir.
            </p>
          </div>
        `,
        icon: 'info',
        confirmButtonText: 'Tamam'
      });
    });
  });
</script>