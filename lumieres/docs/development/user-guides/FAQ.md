# ❓ FAQ DCPrism - Questions Fréquentes

Réponses aux questions les plus courantes par rôle utilisateur

---

## 🎪 **Manager Festival**

### **Q: Comment créer un nouveau film pour mon festival ?**
**R:** 
1. Allez dans **Films** → **Nouveau Film**  
2. Saisissez le titre et l'email de la source
3. Sélectionnez les versions linguistiques attendues (VF, VOST, VO)
4. Le système génère automatiquement la nomenclature et invite la source

### **Q: Pourquoi je ne vois pas mes films ?**
**R:** Vérifiez que vous avez bien **sélectionné votre festival** dans le menu utilisateur en haut à droite. Sans sélection de festival, aucune donnée n'est accessible.

### **Q: Comment relancer une source qui n'a pas uploadé ?**
**R:** Dans **Sources** → sélectionnez la source → **Actions** → **Envoyer Rappel**. Le système envoie automatiquement un email avec les instructions et liens d'upload.

### **Q: Quand puis-je télécharger les DCPs validés ?**
**R:** Dès qu'un DCP affiche le statut **"Validé"** (pastille verte), il est disponible au téléchargement via le bouton **"Télécharger"**.

### **Q: Comment savoir si toutes les versions sont uploadées ?**
**R:** Le dashboard affiche un indicateur de progression par film : "2/3 versions reçues". La barre de progression devient verte à 100%.

---

## 📤 **Source (Producteur/Distributeur)**

### **Q: Je n'arrive pas à me connecter avec mes identifiants**
**R:** Votre compte est créé automatiquement quand un Manager vous assigne un film. Utilisez **"Mot de passe oublié"** pour définir/redéfinir votre mot de passe.

### **Q: Quelle est la taille maximale pour un DCP ?**
**R:** **4GB par fichier**. Si votre DCP est plus volumineux, compressez-le en ZIP ou TAR. Le système gère automatiquement les uploads multipart.

### **Q: Mon upload échoue, que faire ?**
**R:** 
- Vérifiez votre connexion internet
- Le système reprend automatiquement l'upload interrompu
- Si le problème persiste, contactez support@dcprism.com

### **Q: Comment savoir si mon DCP est validé ?**
**R:** Vous recevez un **email de confirmation** + notification dans votre tableau de bord. Le statut passe de "Analysé" à "Validé".

### **Q: Puis-je uploader plusieurs versions du même film ?**
**R:** Oui, sélectionnez simplement la version linguistique correspondante (VF, VOST, etc.) lors de chaque upload.

### **Q: Mon DCP a été rejeté, pourquoi ?**
**R:** Consultez les **"Détails techniques"** dans votre dashboard. Les raisons courantes : format incorrect, checksums invalides, métadonnées manquantes.

---

## 🔧 **Technicien Validation**

### **Q: Comment prioriser les validations urgentes ?**
**R:** La queue affiche automatiquement les DCPs par **priorité** : festiaux approchants, demandes urgentes, puis ordre d'arrivée.

### **Q: Que vérifier lors d'une validation DCP ?**
**R:**
- ✅ **Structure** : Présence des fichiers obligatoires (CPL, PKL, ASSETMAP)
- ✅ **Métadonnées** : Cohérence durée, résolution, fréquence  
- ✅ **Checksums** : Intégrité des fichiers
- ✅ **Conformité** : Respect normes DCI/SMPTE

### **Q: Comment rejeter un DCP avec feedback détaillé ?**
**R:** 
1. Cliquez **"Rejeter"** 
2. **Sélectionnez la catégorie** d'erreur (technique, format, métadonnées)
3. **Ajoutez notes détaillées** pour aider la source à corriger
4. Le système notifie automatiquement la source

### **Q: Puis-je valider plusieurs DCPs en une fois ?**
**R:** Oui, cochez les DCPs concernés puis utilisez **"Actions groupées"** → **"Valider sélection"**. Disponible uniquement pour les DCPs sans problème détecté.

### **Q: Comment gérer les DCPs chiffrés ?**
**R:** Les DCPs chiffrés sont détectés automatiquement. Validez la structure, les KDM seront gérés par le cinéma lors du téléchargement.

---

## 🎭 **Cinema (Exploitation)**

### **Q: Comment trouver des DCPs pour mon festival ?**
**R:** Utilisez les **filtres** : sélectionnez votre festival, puis filtrez par genre, durée ou langue selon votre programmation.

### **Q: Le téléchargement est-il sécurisé ?**
**R:** Oui, chaque lien de téléchargement est **temporaire et sécurisé**. Les checksums fournis permettent de vérifier l'intégrité.

### **Q: Combien de temps ai-je pour télécharger ?**
**R:** Les liens expirent après **7 jours**. Vous pouvez regénérer un nouveau lien si nécessaire.

### **Q: Comment vérifier la compatibilité avec mon équipement ?**
**R:** Les **métadonnées techniques** affichent : résolution, fréquence, codec, encryption. Vérifiez la compatibilité avec votre serveur/projecteur.

### **Q: Que faire si le DCP ne fonctionne pas en projection ?**
**R:** 
1. Vérifiez les checksums téléchargés
2. Contrôlez la compatibilité formats  
3. Contactez le technicien validateur via le système

---

## 👀 **Supervisor**

### **Q: Comment identifier les goulets d'étranglement ?**
**R:** Surveillez les **métriques temps réel** :
- Queue validations trop importante = manque techniciens
- Taux échec uploads élevé = problèmes sources
- Délais festival = problèmes organisation

### **Q: Comment exporter les données pour analyse ?**
**R:** Chaque dashboard propose un bouton **"Export"** (CSV, PDF). Sélectionnez la période et les métriques souhaitées.

### **Q: Que faire en cas d'alertes système ?**
**R:** Les alertes critiques sont remontées automatiquement au SuperAdmin. Documentez et escaladez les problèmes récurrents.

---

## 🔧 **Questions Techniques Générales**

### **Q: Quels formats de DCP sont supportés ?**
**R:**
- **Archives** : ZIP, TAR, RAR  
- **Non-compressés** : Dossiers via upload multiple
- **Limites** : 4GB par fichier, formats DCI/SMPTE standard

### **Q: Comment fonctionne la nomenclature automatique ?**
**R:** Basée sur les règles configurées par festival :
```
[FESTIVAL]_[TITRE]_[VERSION]_[FORMAT]_[DATE]
Ex: CANNES_TITANIC_VF_2K_20250315
```

### **Q: Que faire si j'ai oublié mon mot de passe ?**
**R:**
1. Page de connexion → **"Mot de passe oublié"**
2. Saisissez votre **email**  
3. Suivez les instructions reçues par email
4. Définissez un nouveau mot de passe

### **Q: Comment activer la double authentification ?**
**R:**
1. **Menu utilisateur** → **"Profil"**
2. Section **"Sécurité"** → **"Activer 2FA"**
3. Scannez le QR Code avec votre app (Google Authenticator, Authy)
4. Confirmez avec le code généré

### **Q: Les données sont-elles sauvegardées ?**
**R:** Oui, sauvegardes automatiques :
- **Quotidiennes** : Base de données
- **Temps réel** : Stockage fichiers (Backblaze B2)
- **Rétention** : 30 jours minimum

### **Q: Comment signaler un bug ou problème ?**
**R:**
1. **Email** : support@dcprism.com
2. **Informations à fournir** :
   - Rôle utilisateur
   - Action effectuée  
   - Message d'erreur exact
   - Capture d'écran si possible

---

## 🚨 **Situations d'Urgence**

### **Q: Festival dans 24h, DCPs pas encore validés**
**R:**
1. Contactez **immédiatement** le SuperAdmin
2. Les techniciens peuvent **prioriser** manuellement
3. Validation express possible pour DCPs critiques

### **Q: Upload impossible le jour J**
**R:**
1. **Alternative** : Contact direct Manager → Source par téléphone
2. **Upload physique** : Livraison directe si géographiquement possible
3. **Report** : Négociation avec festival pour séances alternatives

### **Q: Panne système générale**  
**R:**
- **Status** automatique sur status.dcprism.com
- **Notifications** par email des incidents
- **Communication** : Via réseaux sociaux @DCPrism

---

## 📞 **Contacts Support**

- **Support Technique** : support@dcprism.com  
- **Formation Utilisateur** : training@dcprism.com
- **Urgences** : Système de notifications intégré
- **Commercial** : sales@dcprism.com

### Heures d'Ouverture Support
- **Standard** : Lundi-Vendredi 9h-18h CET
- **Urgences Festival** : 24h/7j pendant saisons festivals
- **Maintenance** : Dimanche 2h-4h CET (notification préalable)

---

*FAQ DCPrism - Questions Fréquentes*  
*Version 1.0 - Mise à jour 1er septembre 2025*
